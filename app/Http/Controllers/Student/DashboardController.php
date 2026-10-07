<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $enrollments = Enrollment::with(['course', 'certificate'])
            ->where('user_id', $user->id)
            ->latest('updated_at')
            ->get();

        $active = $enrollments->filter(fn ($e) => $e->isActive() && ! $e->completed_at);
        $stats = [
            'in_progress' => $active->count(),
            'completed' => $enrollments->whereNotNull('completed_at')->count(),
            'certificates' => $enrollments->filter(fn ($e) => $e->certificate)->count(),
            'lessons' => LessonProgress::whereIn('enrollment_id', $enrollments->pluck('id'))->whereNotNull('completed_at')->count(),
        ];

        // "Continue learning": the active course they touched most recently
        $last = LessonProgress::whereIn('enrollment_id', $active->pluck('id'))->latest('updated_at')->first();
        $continue = $last ? $active->firstWhere('id', $last->enrollment_id) : $active->first();
        $nextLesson = $continue ? $this->nextLesson($continue) : null;

        $suggested = Course::where('status', 'published')
            ->whereNotIn('id', $enrollments->pluck('course_id'))
            ->withCount('lessons')
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('student.dashboard', compact('user', 'enrollments', 'stats', 'continue', 'nextLesson', 'suggested'));
    }

    public function certificates()
    {
        $enrollments = Enrollment::with(['course', 'certificate'])
            ->where('user_id', Auth::id())
            ->whereHas('certificate')
            ->latest('completed_at')
            ->get();

        return view('student.certificates', compact('enrollments'));
    }

    private function nextLesson(Enrollment $enrollment): ?Lesson
    {
        $done = $enrollment->progress()->whereNotNull('completed_at')->pluck('lesson_id');

        return $enrollment->course->lessons()
            ->orderBy('sections.position')
            ->orderBy('lessons.position')
            ->whereNotIn('lessons.id', $done)
            ->first();
    }
}
