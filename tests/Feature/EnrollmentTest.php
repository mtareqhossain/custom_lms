<?php

use App\Enums\EnrollmentSource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;

it('grants free access and redirects to the lesson', function () {
    $user = User::factory()->create();
    $user->assignRole('student');
    $course = Course::factory()->withLessons()->create();

    $this->actingAs($user)->post(route('courses.enroll', $course))
        ->assertRedirect(route('learn.show', $course));

    expect(Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->exists())->toBeTrue();
});

it('sets expires_at from the course access_days', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['access_days' => 30]);

    $enrollment = Enrollment::grant($user, $course, EnrollmentSource::Free);

    expect($enrollment->expires_at)->not->toBeNull()
        ->and($enrollment->expires_at->diffInDays(now()->addDays(30)))->toBeLessThan(1);
});

it('gives lifetime access when access_days is null', function () {
    $enrollment = Enrollment::grant(User::factory()->create(), Course::factory()->create(['access_days' => null]), EnrollmentSource::Free);

    expect($enrollment->expires_at)->toBeNull()
        ->and($enrollment->isActive())->toBeTrue();
});

it('blocks the learn page once access has expired', function () {
    $user = User::factory()->create();
    $course = Course::factory()->withLessons()->create();
    Enrollment::create([
        'user_id' => $user->id, 'course_id' => $course->id, 'source' => 'free',
        'starts_at' => now()->subDays(10), 'expires_at' => now()->subDay(),
    ]);

    $this->actingAs($user)->get(route('learn.show', $course))
        ->assertRedirect(route('courses.show', $course));
});

it('does not duplicate an enrollment on repeat grant', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create();

    Enrollment::grant($user, $course, EnrollmentSource::Free);
    Enrollment::grant($user, $course, EnrollmentSource::Free);

    expect(Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->count())->toBe(1);
});
