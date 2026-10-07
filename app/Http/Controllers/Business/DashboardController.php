<?php
namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Replaces the Phase 4 controller: richer overview plus Team and Certificates pages. */
class DashboardController extends Controller
{
    private function orgId(): int
    {
        return (int) Auth::user()->organization_id;
    }

    public function index()
    {
        $orgId = $this->orgId();
        $base = fn () => Enrollment::where('organization_id', $orgId);

        $total = $base()->count();
        $completed = $base()->whereNotNull('completed_at')->count();

        $stats = [
            'students' => User::where('organization_id', $orgId)->role('student')->count(),
            'enrollments' => $total,
            'completion_rate' => $total ? (int) round($completed / $total * 100) : 0,
            'active_week' => LessonProgress::whereHas('enrollment', fn ($q) => $q->where('organization_id', $orgId))
                ->where('updated_at', '>=', now()->subDays(7))->distinct()->count('enrollment_id'),
            'certificates' => Certificate::whereHas('enrollment', fn ($q) => $q->where('organization_id', $orgId))->count(),
        ];

        $courses = Course::whereHas('enrollments', fn ($q) => $q->where('organization_id', $orgId))
            ->withCount([
                'enrollments as enrolled_count' => fn ($q) => $q->where('organization_id', $orgId),
                'enrollments as completed_count' => fn ($q) => $q->where('organization_id', $orgId)->whereNotNull('completed_at'),
            ])
            ->get();

        $notStarted = $base()->with(['user', 'course'])
            ->where('progress_percent', 0)->whereNull('completed_at')
            ->where('created_at', '<=', now()->subDays(7))
            ->latest()->limit(6)->get();

        $expiring = $base()->with(['user', 'course'])
            ->whereNull('completed_at')
            ->whereBetween('expires_at', [now(), now()->addDays(14)])
            ->orderBy('expires_at')->limit(6)->get();

        $recent = $base()->with(['user', 'course'])
            ->whereNotNull('completed_at')
            ->latest('completed_at')->limit(6)->get();

        return view('business.dashboard', compact('stats', 'courses', 'notStarted', 'expiring', 'recent'));
    }

    public function course(Course $course)
    {
        $enrollments = $course->enrollments()
            ->where('organization_id', $this->orgId())
            ->with(['user', 'certificate'])
            ->orderByDesc('progress_percent')
            ->get();

        abort_if($enrollments->isEmpty(), 404);

        return view('business.course', compact('course', 'enrollments'));
    }

    public function students(Request $request)
    {
        $orgId = $this->orgId();

        $enrollments = Enrollment::with(['user', 'course', 'certificate'])
            ->where('organization_id', $orgId)
            ->when($request->q, fn ($q, $s) => $q->whereHas('user', fn ($u) => $u->where(fn ($w) => $w
                ->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('employee_ref', 'like', "%{$s}%"))))
            ->when($request->course, fn ($q, $c) => $q->where('course_id', $c))
            ->when($request->status, function ($q, $status) {
                match ($status) {
                    'completed' => $q->whereNotNull('completed_at'),
                    'not_started' => $q->where('progress_percent', 0)->whereNull('completed_at'),
                    'in_progress' => $q->where('progress_percent', '>', 0)->whereNull('completed_at'),
                    'expired' => $q->whereNull('completed_at')->whereNotNull('expires_at')->where('expires_at', '<', now()),
                    default => null,
                };
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $courses = Course::whereHas('enrollments', fn ($q) => $q->where('organization_id', $orgId))->orderBy('title')->get(['id', 'title']);

        return view('business.students', compact('enrollments', 'courses'));
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
