<?php


use App\Enums\EnrollmentSource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;

function stripeSignedPost(string $uri, array $payload, ?string $secret = null)
{
    $secret ??= config('cashier.webhook.secret') ?: 'whsec_test';
    $body = json_encode($payload);
    $timestamp = time();
    $signature = hash_hmac('sha256', "{$timestamp}.{$body}", $secret);

    return test()->withHeaders(['Stripe-Signature' => "t={$timestamp},v1={$signature}"])
        ->call('POST', $uri, [], [], [], ['CONTENT_TYPE' => 'application/json'], $body);
}

beforeEach(function () {
    config(['cashier.webhook.secret' => 'whsec_test']);
});

function checkoutPayload(User $user, Course $course, string $sessionId = 'cs_test_1'): array
{
    return [
        'id' => 'evt_'.$sessionId,
        'type' => 'checkout.session.completed',
        'data' => ['object' => [
            'id' => $sessionId,
            'payment_intent' => null,
            'amount_total' => $course->price_cents,
            'currency' => strtolower($course->currency),
            'metadata' => ['user_id' => (string) $user->id, 'course_id' => (string) $course->id],
        ]],
    ];
}

it('grants access when the webhook fires with a valid signature', function () {
    $user = User::factory()->create();
    $course = Course::factory()->paid()->create();

    $response = stripeSignedPost('/stripe/webhook', checkoutPayload($user, $course));

    $response->assertOk();
    expect(Order::where('stripe_checkout_session_id', 'cs_test_1')->where('status', 'paid')->exists())->toBeTrue();
    $enrollment = Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->first();
    expect($enrollment)->not->toBeNull()->and($enrollment->source)->toBe(EnrollmentSource::Purchase);
});

it('rejects a webhook with a bad signature and creates nothing', function () {
    $user = User::factory()->create();
    $course = Course::factory()->paid()->create();

    $response = stripeSignedPost('/stripe/webhook', checkoutPayload($user, $course, 'cs_test_bad'), 'wrong_secret');

    $response->assertStatus(400);
    expect(Order::where('stripe_checkout_session_id', 'cs_test_bad')->exists())->toBeFalse();
});

it('does not double-process a retried webhook delivery', function () {
    $user = User::factory()->create();
    $course = Course::factory()->paid()->create();
    $payload = checkoutPayload($user, $course, 'cs_test_retry');

    stripeSignedPost('/stripe/webhook', $payload)->assertOk();
    stripeSignedPost('/stripe/webhook', $payload)->assertOk(); // Stripe retries on timeout/5xx

    expect(Order::where('stripe_checkout_session_id', 'cs_test_retry')->count())->toBe(1);
});

it('renews an expired enrollment instead of duplicating it', function () {
    $user = User::factory()->create();
    $course = Course::factory()->paid(4900, 30)->create();
    Enrollment::create([
        'user_id' => $user->id, 'course_id' => $course->id, 'source' => 'purchase',
        'starts_at' => now()->subDays(60), 'expires_at' => now()->subDays(30),
    ]);

    stripeSignedPost('/stripe/webhook', checkoutPayload($user, $course, 'cs_test_renew'))->assertOk();

    $enrollments = Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->get();
    expect($enrollments)->toHaveCount(1)
        ->and($enrollments->first()->isActive())->toBeTrue();
});
