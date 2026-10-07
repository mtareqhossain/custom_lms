<?php

use App\Enums\EnrollmentSource;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Str;

function certificateFor(User $user, Course $course, ?int $orgId = null): Certificate
{
    $enrollment = Enrollment::grant($user, $course, $orgId ? EnrollmentSource::Business : EnrollmentSource::Free, $orgId);
    $enrollment->update(['completed_at' => now(), 'progress_percent' => 100]);

    return Certificate::create(['enrollment_id' => $enrollment->id, 'code' => (string) Str::uuid(), 'issued_at' => now()]);
}

it('lets the certificate owner download it', function () {
    $user = User::factory()->create();
    $certificate = certificateFor($user, Course::factory()->create());

    $this->actingAs($user)->get(route('certificates.download', $certificate->code))->assertOk();
});

it('blocks a stranger from downloading someone elses certificate', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $certificate = certificateFor($owner, Course::factory()->create());

    $this->actingAs($stranger)->get(route('certificates.download', $certificate->code))->assertForbidden();
});

it('lets someone in the same organization download it', function () {
    $org = Organization::create(['name' => 'A', 'slug' => 'org-cert', 'contact_email' => 'cert@test.com']);
    $student = User::factory()->create(['organization_id' => $org->id]);
    $bizAdmin = User::factory()->create(['organization_id' => $org->id]);
    $certificate = certificateFor($student, Course::factory()->create(), $org->id);

    $this->actingAs($bizAdmin)->get(route('certificates.download', $certificate->code))->assertOk();
});

it('shows the public verification page to anyone, logged in or not', function () {
    $certificate = certificateFor(User::factory()->create(), Course::factory()->create());

    $this->get(route('certificates.show', $certificate->code))->assertOk()->assertSee('verified');
});
