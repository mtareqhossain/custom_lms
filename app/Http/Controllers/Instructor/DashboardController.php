<?php
namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user->hasAnyRole(['admin', 'instructor']), 403);

        $courses = Course::query()
            ->when(! $user->hasRole('admin'), fn ($q) => $q->where('instructor_id', $user->id))
            ->withCount([
                'enrollments',
                'enrollments as completed_count' => fn ($q) => $q->whereNotNull('completed_at'),
            ])
            ->withAvg('enrollments as avg_progress', 'progress_percent')
            ->latest()
            ->get();

        $ids = $courses->pluck('id');
        $learners = Enrollment::whereIn('course_id', $ids)->distinct()->count('user_id');
        $avgProgress = (int) round(Enrollment::whereIn('course_id', $ids)->avg('progress_percent') ?? 0);
        $activeThisWeek = LessonProgress::whereHas('enrollment', fn ($q) => $q->whereIn('course_id', $ids))
            ->where('updated_at', '>=', now()->subDays(7))
            ->distinct()->count('enrollment_id');

        return view('instructor.dashboard', compact('courses', 'learners', 'avgProgress', 'activeThisWeek'));
    }

    public function course(Course $course)
    {
        $this->authorizeCourse($course);

        $course->load('sections.lessons');
        $enrollments = $course->enrollments()->with('user')->orderByDesc('progress_percent')->get();

        $lessonIds = $course->sections->flatMap->lessons->pluck('id');
        $doneByLesson = LessonProgress::whereIn('lesson_id', $lessonIds)
            ->whereNotNull('completed_at')
            ->groupBy('lesson_id')
            ->selectRaw('lesson_id, count(*) as c')
            ->pluck('c', 'lesson_id');

        $lastActive = LessonProgress::whereIn('enrollment_id', $enrollments->pluck('id'))
            ->groupBy('enrollment_id')
            ->selectRaw('enrollment_id, max(updated_at) as last')
            ->pluck('last', 'enrollment_id');

        return view('instructor.course', compact('course', 'enrollments', 'doneByLesson', 'lastActive'));
    }

    public function export(Course $course)
    {
        $this->authorizeCourse($course);

        $enrollments = $course->enrollments()->with('user')->orderBy('id')->get();
        $lastActive = LessonProgress::whereIn('enrollment_id', $enrollments->pluck('id'))
            ->groupBy('enrollment_id')->selectRaw('enrollment_id, max(updated_at) as last')->pluck('last', 'enrollment_id');

        return response()->streamDownload(function () use ($enrollments, $lastActive) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['name', 'email', 'progress_percent', 'status', 'enrolled_at', 'completed_at', 'last_active']);
            foreach ($enrollments as $e) {
                fputcsv($out, [
                    $e->user->name,
                    $e->user->email,
                    $e->progress_percent,
                    $e->completed_at ? 'completed' : ($e->isActive() ? 'in progress' : 'expired'),
                    $e->starts_at?->toDateString(),
                    $e->completed_at?->toDateString(),
                    $lastActive[$e->id] ?? '',
                ]);
            }
            fclose($out);
        }, \Illuminate\Support\Str::slug($course->title).'-progress.csv', ['Content-Type' => 'text/csv']);
    }

    private function authorizeCourse(Course $course): void
    {
        $user = Auth::user();
        abort_unless($user->hasRole('admin') || ($user->hasRole('instructor') && $course->instructor_id === $user->id), 403);
    }
}
