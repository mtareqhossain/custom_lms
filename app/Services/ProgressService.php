<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Quiz;
use Illuminate\Support\Facades\Route;

/**
 * Phase 19's version of ProgressService — adds required-quiz gating on top of
 * Phase 17's notification hooks. If you've made your own further changes to
 * this file, the only NEW logic here vs. Phase 17 is:
 *   - hasPassedAllRequiredQuizzes()
 *   - the extra condition in recalculate() that checks it before certifying
 */
class ProgressService
{
    public function complete(Enrollment $enrollment, Lesson $lesson): LessonProgress
    {
        $progress = LessonProgress::firstOrCreate(
            ['enrollment_id' => $enrollment->id, 'lesson_id' => $lesson->id],
            ['completed_at' => now()]
        );

        if (!$progress->completed_at) {
            $progress->update(['completed_at' => now()]);
        }

        $this->recalculate($enrollment);

        return $progress;
    }

    public function recalculate(Enrollment $enrollment): void
    {
        $course = $enrollment->course;
        $totalLessons = $course->sections->sum(fn ($s) => $s->lessons->count());

        if ($totalLessons === 0) {
            return;
        }

        $completedLessons = LessonProgress::where('enrollment_id', $enrollment->id)
            ->whereNotNull('completed_at')
            ->count();

        $percent = (int) round(($completedLessons / $totalLessons) * 100);

        $enrollment->forceFill(['progress_percent' => $percent])->save();

        // All lessons done is necessary but, if the course has required
        // quizzes, no longer sufficient — the student must also have passed
        // every one of them. This re-runs safely any time a quiz is passed
        // too (see QuizGradingService::finalize()), so completion fires the
        // moment the LAST outstanding requirement (lesson or quiz) is met,
        // whichever that happens to be.
        if ($percent >= 100 && !$enrollment->completed_at && $this->hasPassedAllRequiredQuizzes($enrollment)) {
            $enrollment->forceFill(['completed_at' => now()])->save();

            $certificate = Certificate::firstOrCreate(
                ['enrollment_id' => $enrollment->id],
                ['code' => (string) \Illuminate\Support\Str::uuid(), 'issued_at' => now()]
            );

            $this->sendCompletionNotifications($enrollment, $certificate);
        }
    }

    public function hasPassedAllRequiredQuizzes(Enrollment $enrollment): bool
    {
        $requiredQuizzes = Quiz::where('course_id', $enrollment->course_id)
            ->where('is_required', true)
            ->where('is_active', true)
            ->get();

        if ($requiredQuizzes->isEmpty()) {
            return true;
        }

        $student = $enrollment->user;

        return $requiredQuizzes->every(fn (Quiz $quiz) => $quiz->hasPassed($student));
    }

    /**
     * Fires once, the moment an enrollment crosses 100% AND (if applicable)
     * every required quiz has been passed:
     *  - the student gets a congratulations email with their certificate link
     *  - the course's instructor gets notified (if one is set)
     *  - the enrolling organization's contact gets notified (if this
     *    enrollment came through a business/organization, not a direct signup)
     */
    protected function sendCompletionNotifications(Enrollment $enrollment, Certificate $certificate): void
    {
        $student = $enrollment->user;
        $course = $enrollment->course;

        $certificateUrl = Route::has('certificates.download')
            ? route('certificates.download', ['code' => $certificate->code])
            : url('/certificates/' . $certificate->code . '/download');

        $commonVars = [
            'student_name' => $student->name,
            'course_title' => $course->title,
            'certificate_url' => $certificateUrl,
            'app_name' => config('app.name'),
        ];

        try {
            TemplateMailer::send('course_completed_student', $student->email, $student->name, $commonVars);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed sending student completion email: ' . $e->getMessage());
        }

        $instructor = $course->instructor ?? null;
        if ($instructor && $instructor->email) {
            try {
                TemplateMailer::send(
                    'course_completed_instructor',
                    $instructor->email,
                    $instructor->name,
                    $commonVars + ['instructor_name' => $instructor->name]
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed sending instructor completion email: ' . $e->getMessage());
            }
        }

        if ($enrollment->organization_id) {
            $organization = $enrollment->organization ?? \App\Models\Organization::find($enrollment->organization_id);
            if ($organization && $organization->contact_email) {
                try {
                    TemplateMailer::send(
                        'course_completed_business',
                        $organization->contact_email,
                        $organization->contact_name ?? $organization->name,
                        $commonVars + ['organization_name' => $organization->name]
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Failed sending organization completion email: ' . $e->getMessage());
                }
            }
        }
    }
}
