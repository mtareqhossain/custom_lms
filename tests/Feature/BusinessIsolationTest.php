<?php

use App\Enums\EnrollmentSource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Organization;
use App\Models\User;

function businessUser(Organization $org): User
{
    $user = User::factory()->create(['organization_id' => $org->id]);
    $user->assignRole('business');

    return $user;
}

it('only shows students from the admins own organization', function () {
    $orgA = Organization::create(['name' => 'A', 'slug' => 'org-a', 'contact_email' => 'a@test.com']);
    $orgB = Organization::create(['name' => 'B', 'slug' => 'org-b', 'contact_email' => 'b@test.com']);
    $course = Course::factory()->create();

    $studentA = User::factory()->create(['organization_id' => $orgA->id, 'name' => 'Alpha Student']);
    $studentB = User::factory()->create(['organization_id' => $orgB->id, 'name' => 'Beta Student']);
    Enrollment::grant($studentA, $course, EnrollmentSource::Business, $orgA->id);
    Enrollment::grant($studentB, $course, EnrollmentSource::Business, $orgB->id);

    $admin = businessUser($orgA);

    $response = $this->actingAs($admin)->get(route('business.students'));

    $response->assertOk()->assertSee('Alpha Student')->assertDontSee('Beta Student');
});

it('rejects a student import for a course not assigned to the organization', function () {
    $org = Organization::create(['name' => 'A', 'slug' => 'org-a2', 'contact_email' => 'a2@test.com']);
    $unassignedCourse = Course::factory()->create();
    $admin = businessUser($org);

    $csv = "name,email\r\nJane Doe,jane@example.com\r\n";
    $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('students.csv', $csv);

    $this->actingAs($admin)
        ->post(route('business.import.store'), ['course_id' => $unassignedCourse->id, 'file' => $file])
        ->assertSessionHasErrors('course_id');
});

it('allows a student import for a course assigned to the organization', function () {
    $org = Organization::create(['name' => 'A', 'slug' => 'org-a3', 'contact_email' => 'a3@test.com']);
    $course = Course::factory()->create();
    $org->courses()->attach($course->id);
    $admin = businessUser($org);

    $csv = "name,email\r\nJane Doe,jane2@example.com\r\n";
    $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('students.csv', $csv);

    $this->actingAs($admin)
        ->post(route('business.import.store'), ['course_id' => $course->id, 'file' => $file])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('business.import.create'));
});
