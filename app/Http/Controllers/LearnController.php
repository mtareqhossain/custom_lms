<?php
namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Services\ProgressService;
use App\Services\VideoUrlResolver;

class LearnController extends Controller
{
    public function show(Course $course, VideoUrlResolver $videos, ?Lesson $lesson = null)
    {
        $enrollment = Enrollment::where('user_id', auth()->id())->where('course_id', $course->id)->firstOrFail();

        if (! $enrollment->isActive()) {
            return redirect()->route('courses.show', $course)->with('status', 'Your access to this course has expired.');
        }

        $course->load('sections.lessons');
        $all = $course->sections->flatMap->lessons->values();
        $completed = $enrollment->progress()->whereNotNull('completed_at')->pluck('lesson_id')->all();

        $lesson ??= $all->first(fn ($l) => ! in_array($l->id, $completed)) ?? $all->first();
        abort_unless($lesson && $all->contains('id', $lesson->id), 404);

        $index = $all->search(fn ($l) => $l->id === $lesson->id);

        return view('learn.show', [
            'course' => $course,
            'enrollment' => $enrollment,
            'lesson' => $lesson,
            'completed' => $completed,
            'isDone' => in_array($lesson->id, $completed),
            'prev' => $all[$index - 1] ?? null,
            'next' => $all[$index + 1] ?? null,
            'video' => $videos->resolve($lesson),
            'previousProgress' => session('previous_progress', $enrollment->progress_percent),
        ]);
    }

    public function complete(Course $course, Lesson $lesson, ProgressService $progress)
    {
        $enrollment = Enrollment::with('course')->where('user_id', auth()->id())->where('course_id', $course->id)->firstOrFail();
        abort_unless($enrollment->isActive(), 403);
        abort_unless($course->lessons()->where('lessons.id', $lesson->id)->exists(), 404);

        $before = $enrollment->progress_percent;
        $enrollment = $progress->complete($enrollment, $lesson);

        if ($enrollment->progress_percent === 100) {
            return redirect()->route('my.courses')->with('status', 'Course complete. Your certificate is ready.');
        }

        $all = $course->lessons()->orderBy('sections.position')->orderBy('lessons.position')->get();
        $next = $all->skipUntil(fn ($l) => $l->id === $lesson->id)->skip(1)->first();

        return redirect()
            ->route('learn.show', array_filter([$course, $next]))
            ->with('previous_progress', $before);
    }
}
