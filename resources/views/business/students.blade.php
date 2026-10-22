<x-panel-layout title="Team">
<div class="mx-auto max-w-6xl px-4 py-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="display text-3xl">Team</h1>
            <p class="muted mt-1">Everyone in your organization, with their training history.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('business.roster.export') }}" class="btn btn-quiet">Export records</a>
            <a href="{{ route('business.people.create') }}" class="btn">Add employee</a>
        </div>
    </div>

    @if(session('status'))
        <div class="mt-4 rounded-lg bg-[var(--mint)] px-4 py-3 text-sm font-medium text-[var(--teal-dark)]">{{ session('status') }}</div>
    @endif

    @php
        $fmtHours = function ($seconds) {
            $seconds = (int) ($seconds ?? 0);
            if ($seconds <= 0) return '—';
            $h = intdiv($seconds, 3600);
            $m = intdiv($seconds % 3600, 60);
            return $h > 0 ? "{$h}h {$m}m" : "{$m}m";
        };
    @endphp

    <form method="GET" class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_14rem_11rem_auto_auto]">
        <input name="q" value="{{ request('q') }}" placeholder="Search name, email or staff ID" class="field" aria-label="Search">
        <select name="course" class="field" aria-label="Course">
            <option value="">All courses</option>
            @foreach($courses as $c)<option value="{{ $c->id }}" @selected(request('course') == $c->id)>{{ $c->title }}</option>@endforeach
        </select>
        <select name="status" class="field" aria-label="Status">
            <option value="">Any status</option>
            @foreach(['completed' => 'Completed', 'in_progress' => 'In progress', 'not_started' => 'Not started', 'expired' => 'Expired', 'not_enrolled' => 'Not enrolled'] as $val => $label)
                <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn">Filter</button>
        <a href="{{ route('business.students') }}" class="btn btn-quiet">Reset</a>
    </form>

    <div class="panel mt-6 overflow-x-auto">
        <table class="tbl">
            <thead><tr><th>Employee</th><th>Manager</th><th>Courses</th><th>Completed</th><th>Time spent</th><th></th></tr></thead>
            <tbody>
                @forelse($students as $s)
                    <tr>
                        <td>
                            <a href="{{ route('business.people.show', $s) }}" class="font-medium hover:underline">{{ $s->name }}</a>
                            <p class="muted text-xs">{{ $s->email }}@if($s->employee_ref) &middot; {{ $s->employee_ref }}@endif</p>
                            <div class="mt-1 flex flex-wrap gap-1">
                                @forelse($s->enrollments->take(3) as $e)
                                    <span class="pill {{ $e->completed_at ? '' : 'pill-grey' }} !text-xs">{{ \Illuminate\Support\Str::limit($e->course->title, 20) }}</span>
                                @empty
                                    <span class="pill pill-grey !text-xs">Not enrolled yet</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="text-sm">{{ $s->manager?->name ?? '—' }}</td>
                        <td class="text-sm">{{ $s->completed_count }} / {{ $s->enrollments_count }}</td>
                        <td class="text-sm">{{ $fmtHours($s->seconds_watched_sum) }}</td>
                        <td class="text-right"><a href="{{ route('business.people.edit', $s) }}" class="text-sm font-medium underline">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted !py-10 text-center">No one matches these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $students->links() }}</div>
</div>
</x-panel-layout>
