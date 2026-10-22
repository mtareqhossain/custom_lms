<?php
namespace App\Http\Controllers\Business;

use App\Exports\EnrollmentsExport;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\TrainingAssignment;
use App\Models\User;
use App\Notifications\SetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class PeopleController extends Controller
{
    private function orgId(): int
    {
        return (int) Auth::user()->organization_id;
    }

    /** A pure manager (no 'business' role) only ever sees/manages their own direct reports. */
    private function assignableStudents()
    {
        $user = Auth::user();
        $q = User::where('organization_id', $this->orgId())->role('student');

        if ($user->hasRole('manager') && ! $user->hasRole('business')) {
            $q->where('manager_id', $user->id);
        }

        return $q;
    }

    /** Anyone who can be set as someone's manager: managers and business admins in this org. */
    private function possibleManagers()
    {
        return User::where('organization_id', $this->orgId())
            ->role(['manager', 'business'])
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    public function create()
    {
        return view('business.people.create', ['managers' => $this->possibleManagers()]);
    }

    public function store(Request $request)
    {
        $orgId = $this->orgId();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'employee_ref' => ['nullable', 'string', 'max:100'],
            'manager_id' => ['nullable', Rule::exists('users', 'id')->where('organization_id', $orgId)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'employee_ref' => $data['employee_ref'] ?? null,
            'organization_id' => $orgId,
            'manager_id' => $data['manager_id'] ?? null,
            'password' => Hash::make(Str::random(32)),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('student');
        $user->notify(new SetPasswordNotification(Password::createToken($user), Auth::user()->organization?->name));

        return redirect()->route('business.students')->with('status', "{$user->name} added. They'll receive an email to set their password.");
    }

    public function edit(User $user)
    {
        abort_unless($this->assignableStudents()->where('id', $user->id)->exists(), 403);

        return view('business.people.edit', [
            'student' => $user,
            'managers' => $this->possibleManagers()->reject(fn ($m) => $m->id === $user->id),
        ]);
    }

    public function update(Request $request, User $user)
    {
        abort_unless($this->assignableStudents()->where('id', $user->id)->exists(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'employee_ref' => ['nullable', 'string', 'max:100'],
            'manager_id' => ['nullable', Rule::exists('users', 'id')->where('organization_id', $this->orgId())],
        ]);

        abort_if(($data['manager_id'] ?? null) == $user->id, 422, "A learner can't be their own manager.");

        $user->update($data);

        return back()->with('status', 'Updated.');
    }

    public function show(User $user)
    {
        abort_unless($this->assignableStudents()->where('id', $user->id)->exists(), 403);

        $orgId = $this->orgId();

        $enrollments = Enrollment::with(['course', 'certificate'])
            ->where('user_id', $user->id)
            ->where('organization_id', $orgId)
            ->latest()
            ->get();

        $assignments = TrainingAssignment::with('course')
            ->where('user_id', $user->id)
            ->where('organization_id', $orgId)
            ->latest()
            ->get();

        $totalSeconds = $user->lessonProgress()->where('enrollments.organization_id', $orgId)->sum('seconds_watched');

        return view('business.people.show', [
            'student' => $user,
            'enrollments' => $enrollments,
            'assignments' => $assignments,
            'hoursLogged' => round($totalSeconds / 3600, 1),
        ]);
    }

    public function export()
    {
        $visibleIds = $this->assignableStudents()->pluck('id')->all();

        return Excel::download(new EnrollmentsExport($this->orgId(), $visibleIds), 'training-records.xlsx');
    }
}
