<x-panel-layout title="Team">
<div class="mx-auto max-w-6xl px-4 py-8">
    <h1 class="display text-3xl">Team</h1>
    <p class="muted mt-1">Every enrollment across your organization.</p>

    <form method="GET" class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_14rem_11rem_auto_auto]">
        <input name="q" value="{{ request('q') }}" placeholder="Search name, email or staff ID" class="field" aria-label="Search">
        <select name="course" class="field" aria-label="Course">
            <option value="">All courses</option>
            @foreach($courses as $c)<option value="{{ $c->id }}" @selected(request('course') == $c->id)>{{ $c->title }}</option>@endforeach
        </select>
        <select name="status" class="field" aria-label="Status">
            <option value="">Any status</option>
            @foreach(['completed' => 'Completed', 'in_progress' => 'In progress', 'not_started' => 'Not started', 'expired' => 'Expired'] as $val => $label)
                <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn">Filter</button>
        <a href="{{ route('business.students') }}" class="btn btn-quiet">Reset</a>
    </form>

    <div class="panel mt-6 overflow-x-auto">
        <table class="tbl">
            <thead><tr><th>Student</th><th>Course</th><th>Progress</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($enrollments as $e)
                    <tr>
                        <td>
                            <p class="font-medium">{{ $e->user->name }}</p>
                            <p class="muted text-xs">{{ $e->user->email }}@if($e->user->employee_ref) &middot; {{ $e->user->employee_ref }}@endif</p>
                        </td>
                        <td>{{ $e->course->title }}</td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="bar w-28"><span style="width: {{ (int) $e->progress_percent }}%"></span></div>
                                <span class="muted text-xs">{{ (int) $e->progress_percent }}%</span>
                            </div>
                        </td>
                        <td>
                            @if($e->completed_at)<span class="pill">Completed</span>
                            @elseif($e->expires_at && $e->expires_at->isPast())<span class="pill pill-warn">Expired</span>
                            @elseif((int) $e->progress_percent === 0)<span class="pill pill-grey">Not started</span>
                            @else<span class="pill pill-grey">In progress</span>@endif
                        </td>
                        <td class="text-right">
                            @if($e->certificate)<a href="{{ route('certificates.download', $e->certificate->code) }}" class="text-sm font-medium underline">Certificate</a>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted !py-10 text-center">No enrollments match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $enrollments->links() }}</div>
</div>
</x-panel-layout>
