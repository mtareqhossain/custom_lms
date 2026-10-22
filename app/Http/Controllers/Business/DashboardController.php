<?php
namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseOrganization;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\TrainingAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Replaces the Phase 4 controller: richer overview, status/trend/hours charts, Team and Certificates pages. */
class DashboardController extends Controller
{
    private function orgId(): int
    {
        return (int) Auth::user()->organization_id;
    }

    /** A pure manager (no 'business' role) only ever sees their own direct reports. */
    private function assignableStudents()
    {
        $user = Auth::user();
        $q = User::where('organization_id', $this->orgId())->role('student');

        if ($user->hasRole('manager') && ! $user->hasRole('business')) {
            $q->where('manager_id', $user->id);
        }

        return $q;
    }

    public function index()
    {
        $orgId = $this->orgId();
        $base = fn () => Enrollment::where('organization_id', $orgId);
        $notExpired = fn ($q) => $q->where(fn ($w) => $w->whereNull('expires_at')->orWhere('expires_at', '>=', now()));

        $total = $base()->count();
        $completed = $base()->whereNotNull('completed_at')->count();

        $stats = [
            'students' => $this->assignableStudents()->count(),
            'enrollments' => $total,
            'completion_rate' => $total ? (int) round($completed / $total * 100) : 0,
            'active_week' => LessonProgress::whereHas('enrollment', fn ($q) => $q->where('organization_id', $orgId))
                ->where('updated_at', '>=', now()->subDays(7))->distinct()->count('enrollment_id'),
            'certificates' => Certificate::whereHas('enrollment', fn ($q) => $q->where('organization_id', $orgId))->count(),
        ];

        $totalSeconds = LessonProgress::whereHas('enrollment', fn ($q) => $q->where('organization_id', $orgId))->sum('seconds_watched');
        $stats['hours_logged'] = round($totalSeconds / 3600, 1);

        $assignedIds = $this->assignableStudents()->pluck('id');
        $stats['assignments_overdue'] = TrainingAssignment::where('organization_id', $orgId)
            ->whereIn('user_id', $assignedIds)
            ->whereNull('completed_at')
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->count();

        $expired = $base()->whereNull('completed_at')->whereNotNull('expires_at')->where('expires_at', '<', now())->count();
        $inProgress = $notExpired($base()->whereNull('completed_at')->where('progress_percent', '>', 0))->count();
        $notStarted = $notExpired($base()->whereNull('completed_at')->where('progress_percent', 0))->count();

        $statusBreakdown = [
            'completed' => $completed, 'in_progress' => $inProgress,
            'not_started' => $notStarted, 'expired' => $expired,
        ];

        $weeklyCounts = $base()->where('created_at', '>=', now()->subWeeks(7)->startOfWeek())->get()
            ->groupBy(fn ($e) => $e->created_at->startOfWeek()->format('Y-m-d'));

        $trend = collect(range(7, 0))->map(function ($i) use ($weeklyCounts) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            return ['label' => $weekStart->format('j M'), 'count' => $weeklyCounts->get($weekStart->format('Y-m-d'), collect())->count()];
        })->all();

        $seatLimits = CourseOrganization::where('organization_id', $orgId)
            ->pluck('seat_limit', 'course_id');

        $courses = Course::whereHas('enrollments', fn ($q) => $q->where('organization_id', $orgId))
            ->withCount([
                'enrollments as enrolled_count' => fn ($q) => $q->where('organization_id', $orgId),
                'enrollments as completed_count' => fn ($q) => $q->where('organization_id', $orgId)->whereNotNull('completed_at'),
            ])
            ->get()
            ->each(function ($course) use ($seatLimits) {
                $course->seat_limit = $seatLimits[$course->id] ?? null;
            });

        $notStartedList = $base()->with(['user', 'course'])
            ->where('progress_percent', 0)->whereNull('completed_at')
            ->where('created_at', '<=', now()->subDays(7))
            ->latest()->limit(6)->get();

        $expiringList = $base()->with(['user', 'course'])
            ->whereNull('completed_at')
            ->whereBetween('expires_at', [now(), now()->addDays(14)])
            ->orderBy('expires_at')->limit(6)->get();

        $recent = $base()->with(['user', 'course'])
            ->whereNotNull('completed_at')
            ->latest('completed_at')->limit(6)->get();

        return view('business.dashboard', [
            'stats' => $stats,
            'statusBreakdown' => $statusBreakdown,
            'trend' => $trend,
            'courses' => $courses,
            'notStarted' => $notStartedList,
            'expiring' => $expiringList,
            'recent' => $recent,
        ]);
    }

    public function course(Course $course)
    {
        $orgId = $this->orgId();

        $enrollments = $course->enrollments()
            ->where('organization_id', $orgId)
            ->with(['user', 'certificate'])
            ->orderByDesc('progress_percent')
            ->get();

        abort_if($enrollments->isEmpty(), 404);

        $seatLimit = CourseOrganization::where('course_id', $course->id)
            ->where('organization_id', $orgId)
            ->value('seat_limit');

        return view('business.course', compact('course', 'enrollments', 'seatLimit'));
    }

    public function students(Request $request)
    {
        $orgId = $this->orgId();
        $scoped = fn ($q) => $q->where('organization_id', $orgId);

        $students = $this->assignableStudents()
            ->with('manager:id,name')
            ->withCount([
                'enrollments as enrollments_count' => $scoped,
                'enrollments as completed_count' => fn ($q) => $scoped($q)->whereNotNull('completed_at'),
            ])
            ->withSum(['lessonProgress as seconds_watched_sum' => fn ($q) => $q->where('enrollments.organization_id', $orgId)], 'seconds_watched')
            ->with(['enrollments' => fn ($q) => $scoped($q)->with(['course:id,title', 'certificate'])->latest()])
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('employee_ref', 'like', "%{$s}%")))
            ->when($request->course, fn ($q, $c) => $q->whereHas('enrollments', fn ($e) => $scoped($e)->where('course_id', $c)))
            ->when($request->status, function ($q, $status) use ($scoped) {
                match ($status) {
                    'completed' => $q->whereHas('enrollments', fn ($e) => $scoped($e)->whereNotNull('completed_at')),
                    'in_progress' => $q->whereHas('enrollments', fn ($e) => $scoped($e)->where('progress_percent', '>', 0)->whereNull('completed_at')),
                    'not_started' => $q->whereHas('enrollments', fn ($e) => $scoped($e)->where('progress_percent', 0)->whereNull('completed_at')),
                    'expired' => $q->whereHas('enrollments', fn ($e) => $scoped($e)->whereNull('completed_at')->whereNotNull('expires_at')->where('expires_at', '<', now())),
                    'not_enrolled' => $q->whereDoesntHave('enrollments', $scoped),
                    default => null,
                };
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $courses = Course::whereHas('enrollments', fn ($q) => $q->where('organization_id', $orgId))->orderBy('title')->get(['id', 'title']);

        return view('business.students', compact('students', 'courses'));
    }

    public function certificates()
    {
        $certificates = Certificate::with(['enrollment.user', 'enrollment.course'])
            ->whereHas('enrollment', fn ($q) => $q->where('organization_id', $this->orgId()))
            ->latest('issued_at')
            ->paginate(20);

        return view('business.certificates', compact('certificates'));
    }
}
