<?php
namespace App\Http\Controllers;

use App\Enums\EnrollmentSource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

/** Replaces the Phase 3 controller: adds charge.refunded handling. */
class StripeWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                config('cashier.webhook.secret'),
            );
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature failed', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'charge.refunded' => $this->handleRefund($event->data->object),
            default => null,
        };

        return response('ok');
    }

    private function handleCheckoutCompleted(object $session): void
    {
        if (Order::where('stripe_checkout_session_id', $session->id)->exists()) {
            return; // already processed, Stripe retries webhooks
        }

        $user = User::find($session->metadata->user_id ?? null);
        $course = Course::find($session->metadata->course_id ?? null);
        if (! $user || ! $course) {
            Log::error('Stripe webhook: user or course missing', ['session' => $session->id]);
            return;
        }

        $stripe = new StripeClient(config('cashier.secret'));
        $receiptUrl = null;
        if ($session->payment_intent) {
            $intent = $stripe->paymentIntents->retrieve($session->payment_intent, ['expand' => ['latest_charge']]);
            $receiptUrl = $intent->latest_charge->receipt_url ?? null;
        }

        Order::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'stripe_checkout_session_id' => $session->id,
            'stripe_payment_intent_id' => $session->payment_intent,
            'amount_cents' => $session->amount_total,
            'currency' => strtoupper($session->currency),
            'status' => 'paid',
            'receipt_url' => $receiptUrl,
            'paid_at' => now(),
        ]);

        // Renew if it already existed (e.g. previously expired) rather than only creating fresh.
        $enrollment = Enrollment::firstOrNew(['user_id' => $user->id, 'course_id' => $course->id]);
        $enrollment->fill([
            'source' => EnrollmentSource::Purchase,
            'starts_at' => now(),
            'expires_at' => $course->accessExpiryFrom(),
        ]);
        if (! $enrollment->exists) {
            $enrollment->progress_percent = 0;
        }
        $enrollment->save();
    }

    /** Marks the Order refunded and cuts off future access. A certificate already earned is kept. */
    private function handleRefund(object $charge): void
    {
        $paymentIntentId = $charge->payment_intent ?? null;
        if (! $paymentIntentId) {
            return;
        }

        $order = Order::where('stripe_payment_intent_id', $paymentIntentId)->first();
        if (! $order || $order->status === 'refunded') {
            return; // unknown order, or already processed (Stripe retries webhooks)
        }

        $order->update(['status' => 'refunded']);

        $enrollment = Enrollment::where('user_id', $order->user_id)->where('course_id', $order->course_id)->first();
        if ($enrollment && ! $enrollment->completed_at) {
            $enrollment->update(['expires_at' => now()]);
        }
    }
}
