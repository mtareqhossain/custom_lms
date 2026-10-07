<?php

use App\Enums\EnrollmentSource;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\ProgressService;

it('reaches 100 percent and issues a certificate on the final lesson', function () {
    $course = Course::factory()->withLessons(3)->create();
    $enrollment = Enrollment::grant(User::factory()->create(), $course, EnrollmentSource::Free);
    $service = app(ProgressService::class);

    $lessons = $course->lessons()->orderBy('lessons.position')->get();
    foreach ($lessons->take(2) as $lesson) {
        $enrollment = $service->complete($enrollment, $lesson);
    }
    expect($enrollment->progress_percent)->toBe(66)->and($enrollment->completed_at)->toBeNull();

    $enrollment = $service->complete($enrollment, $lessons->last());

    expect($enrollment->progress_percent)->toBe(100)
        ->and($enrollment->completed_at)->not->toBeNull()
        ->and(Certificate::where('enrollment_id', $enrollment->id)->count())->toBe(1);
});

it('does not issue a second certificate if a lesson is completed again', function () {
    $course = Course::factory()->withLessons(1)->create();
    $enrollment = Enrollment::grant(User::factory()->create(), $course, EnrollmentSource::Free);
    $service = app(ProgressService::class);
    $lesson = $course->lessons()->first();

    $service->complete($enrollment, $lesson);
    $service->complete($enrollment->fresh(), $lesson);

    expect(Certificate::where('enrollment_id', $enrollment->id)->count())->toBe(1);
});
