<?php
namespace Database\Seeders;

use App\Enums\EnrollmentSource;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Organization;
use App\Models\Section;
use App\Models\User;
use App\Services\ProgressService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/** Local demo data. Every account uses the password "password". Safe to run more than once. */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'instructor', 'business', 'student'] as $r) {
            Role::findOrCreate($r);
        }

        $instructor = $this->user('Ines Instructor', 'instructor@lms.test', 'instructor');
        $org = Organization::firstOrCreate(['slug' => 'acme-ltd'], ['name' => 'Acme Ltd', 'contact_email' => 'business@lms.test']);
        $this->user('Bea Business', 'business@lms.test', 'business', $org->id);

        $independent = $this->user('Sasha Solo', 'student@lms.test', 'student');
        $names = ['Ada Lovelace', 'Ben Carter', 'Chloe Park', 'Dev Patel', 'Eli Novak'];
        $team = [];
        foreach ($names as $i => $name) {
            $team[] = $this->user($name, 'student'.($i + 1).'@lms.test', 'student', $org->id);
        }

        $category = Category::firstOrCreate(['slug' => 'compliance'], ['name' => 'Compliance']);

        $safety = $this->course($instructor, $category, 'Workplace Safety Essentials', 'beginner', 'free', 0, null, [
            'Getting started' => ['Welcome and goals', 'Spotting hazards', 'Your responsibilities'],
            'Core skills' => ['Working safely', 'Reporting incidents', 'Final check'],
        ]);
        $data = $this->course($instructor, $category, 'Data Protection Basics', 'beginner', 'free', 0, null, [
            'Foundations' => ['What is personal data?', 'Why it matters', 'Common mistakes'],
            'In practice' => ['Handling requests', 'Keeping data secure', 'Quick quiz recap'],
        ]);
        $comms = $this->course($instructor, $category, 'Manager Communication Skills', 'intermediate', 'paid', 4900, 90, [
            'Listening' => ['Active listening', 'Asking better questions'],
            'Feedback' => ['Giving feedback', 'Receiving feedback', 'Hard conversations'],
        ]);
        $this->course($instructor, $category, 'Fire Marshal Training', 'advanced', 'paid', 8900, null, [
            'Prevention' => ['Fire risk assessment', 'Extinguisher types'],
            'Response' => ['Evacuation planning', 'Running a drill'],
        ]);

        // Courses Acme is allowed to enroll students in
        $org->courses()->syncWithoutDetaching([$safety->id, $data->id, $comms->id]);

        // Enrollments and progress so the dashboards have something to show
        $this->enroll($team[0], $safety, $org->id, 6); // finished -> certificate
        $this->enroll($team[1], $safety, $org->id, 3);
        $this->enroll($team[2], $safety, $org->id, 1);
        $this->enroll($team[3], $safety, $org->id, 0);
        $this->enroll($team[4], $data, $org->id, 4);
        $this->enroll($team[0], $data, $org->id, 2);

        $e = Enrollment::grant($independent, $safety, EnrollmentSource::Free);
        $this->progress($e, $safety, 2);
    }

    private function user(string $name, string $email, string $role, ?int $orgId = null): User
    {
        $user = User::firstOrCreate(['email' => $email], [
            'name' => $name,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'organization_id' => $orgId,
        ]);
        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }

        return $user;
    }

    private function course(User $instructor, Category $category, string $title, string $level, string $pricing, int $cents, ?int $days, array $sections): Course
    {
        $course = Course::updateOrCreate(['slug' => Str::slug($title)], [
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'title' => $title,
            'summary' => 'A short, practical course on '.Str::lower($title).' for busy teams.',
            'description' => '<p>This demo course shows how content, progress and certificates fit together.</p>',
            'level' => $level,
            'status' => 'published',
            'pricing_type' => $pricing,
            'price_cents' => $cents,
            'currency' => 'GBP',
            'access_days' => $days,
            'published_at' => now(),
        ]);

        $s = 0;
        foreach ($sections as $sectionTitle => $lessons) {
            $section = Section::firstOrCreate(['course_id' => $course->id, 'title' => $sectionTitle], ['position' => ++$s]);
            foreach ($lessons as $j => $lessonTitle) {
                Lesson::firstOrCreate(['section_id' => $section->id, 'title' => $lessonTitle], [
                    'type' => 'text',
                    'content' => '<p>Demo content for <strong>'.e($lessonTitle).'</strong>. Replace this with real material in the studio.</p>',
                    'duration_seconds' => 300,
                    'position' => $j + 1,
                    'is_preview' => $s === 1 && $j === 0,
                ]);
            }
        }

        return $course;
    }

    private function enroll(User $user, Course $course, int $orgId, int $lessonsDone): void
    {
        $enrollment = Enrollment::grant($user, $course, EnrollmentSource::Business, $orgId);
        $this->progress($enrollment, $course, $lessonsDone);
    }

    private function progress(Enrollment $enrollment, Course $course, int $count): void
    {
        $svc = app(ProgressService::class);
        $lessons = $course->lessons()->orderBy('sections.position')->orderBy('lessons.position')->take($count)->get();
        foreach ($lessons as $lesson) {
            $enrollment = $svc->complete($enrollment, $lesson);
        }
    }
}
