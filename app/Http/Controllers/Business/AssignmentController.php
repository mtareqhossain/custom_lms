<?php
namespace App\Http\Controllers\Business;

use App\Enums\EnrollmentSource;
use App\Exceptions\SeatLimitExceededException;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseOrganization;
use App\Models\Enrollment;
use App\Models\TrainingAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    private function orgId(): int
    {
        return (int) Auth::user()->organization_id;
    }

    /** A pure manager (no 'business' role) only ever sees/assigns their own direct reports. */
    private function assignableStudents()
    {
        $user = Auth::user();
        $q = User::where('organization_id', $this->orgId())->role('student');

        if ($user->hasRole('manager') && ! $user->hasRole('business')) {
            $q->where('manager_id', $user->id);
        }

        return $q;
    }

    public function index(Request $request)
    {
        $orgId = $this->orgId();
        $visibleIds = $this->assignableStudents()->pluck('id');

        $assignments = TrainingAssignment::with(['course', 'user'])
            ->where('organization_id', $orgId)
            ->whereIn('user_id', $visibleIds)
            ->when($request->status, function ($q, $status) {
                match ($status) {
                    'overdue' => $q->whereNull('completed_at')->whereNotNull('due_at')->where('due_at', '<', now()),
                    'due_soon' => $q->whereNull('completed_at')->whereNotNull('due_at')->whereBetween('due_at', [now(), now()->addDays(3)]),
                    'completed' => $q->whereNotNull('completed_at'),
                    'assigned' => $q->whereNull('completed_at')->where(fn ($w) => $w->whereNull('due_at')->orWhere('due_at', '>', now()->addDays(3))),
                    default => null,
                };
            })
            ->when($request->course, fn ($q, $c) => $q->where('course_id', $c))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $courses = Course::whereIn('id', CourseOrganization::where('organization_id', $orgId)->pluck('course_id'))
            ->orderBy('title')->get(['id', 'title']);

        return view('business.assignments.index', compact('assignments', 'courses'));
    }

    public function create()
    {
        $students = $this->assignableStudents()->orderBy('name')->get(['id', 'name', 'email']);
        $courses = Course::whereIn('id', CourseOrganization::where('organization_id', $this->orgId())->pluck('course_id'))
            ->orderBy('title')->get(['id', 'title']);

        return view('business.assignments.create', compact('students', 'courses'));
    }

    public function store(Request $request)
    {
        $orgId = $this->orgId();
        $assignableIds = $this->assignableStudents()->pluck('id')->all();

        $data = $request->validate([
            'course_id' => ['required', Rule::exists('course_organization', 'course_id')->where('organization_id', $orgId)],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', Rule::in($assignableIds)],
            'due_mode' => ['required', Rule::in(['none', 'fixed', 'rolling'])],
            'due_date' => ['nullable', 'date', 'required_if:due_mode,fixed'],
            'due_days' => ['nullable', 'integer', 'min:1', 'required_if:due_mode,rolling'],
            'recurrence' => ['nullable', Rule::in(array_keys(TrainingAssignment::RECURRENCES))],
        ]);

        $course = Course::findOrFail($data['course_id']);

        $dueAt = match ($data['due_mode']) {
            'fixed' => Carbon::parse($data['due_date'])->endOfDay(),
            'rolling' => now()->addDays((int) $data['due_days']),
            default => null,
        };

        $created = 0;
        $skipped = 0;

        foreach ($data['user_ids'] as $userId) {
            $student = User::find($userId);
            if (! $student) continue;

            $alreadyOpen = TrainingAssignment::where('course_id', $course->id)
                ->where('user_id', $student->id)
                ->whereNull('completed_at')
                ->exists();

            if ($alreadyOpen) { $skipped++; continue; }

            try {
                $enrollment = Enrollment::grant($student, $course, EnrollmentSource::Business, $orgId);
            } catch (SeatLimitExceededException $e) {
                $skipped++;
                continue;
            }

            TrainingAssignment::create([
                'organization_id' => $orgId,
                'course_id' => $course->id,
                'user_id' => $student->id,
                'assigned_by_id' => Auth::id(),
                'enrollment_id' => $enrollment->id,
                'due_at' => $dueAt,
                'recurrence' => $data['recurrence'] ?: null,
                'completed_at' => $enrollment->completed_at,
            ]);

            $created++;
        }

        $message = "Assigned to {$created} learner(s).";
        if ($skipped) $message .= " Skipped {$skipped} (already assigned, or no seats left).";

        return redirect()->route('business.assignments.index')->with('status', $message);
    }

    public function destroy(TrainingAssignment $assignment)
    {
        abort_unless($assignment->organization_id === $this->orgId(), 403);
        abort_if($assignment->completed_at, 422, "A completed assignment can't be removed.");

        $assignment->delete();

        return back()->with('status', 'Assignment removed.');
    }
}
