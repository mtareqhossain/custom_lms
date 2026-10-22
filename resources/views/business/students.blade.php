<x-panel-layout title="Team">
<div class="mx-auto max-w-6xl px-4 py-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="display text-3xl">Team</h1>
            <p class="muted mt-1">Every student in your organization, enrolled or not.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('business.roster.export') }}" class="btn btn-quiet">Export records</a>
            <a href="{{ route('business.people.create') }}" class="btn">Add employee</a>
        </div>
    </div>

    @if(session('status'))
        <div class="mt-4 rounded-lg bg-[var(--mint)] px-4 py-3 text-sm font-medium text-[var(--teal-dark)]">{{ session('status') }}</div>
    @endif

    <form method="GET" class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_14rem_12rem_auto_auto]">
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
            <thead><tr><th>Student</th><th>Manager</th><th>Courses</th><th>Enrolled</th><th>Completed</th><th></th></tr></thead>
            <tbody>
                @forelse($students as $student)
                    <tr>
                        <td>
                            <a href="{{ route('business.people.show', $student) }}" class="font-medium hover:underline">{{ $student->name }}</a>
                            <p class="muted text-xs">{{ $student->email }}@if($student->employee_ref) &middot; {{ $student->employee_ref }}@endif</p>
                        </td>
                        <td class="text-sm">{{ $student->manager?->name ?? '—' }}</td>
                        <td>
                            @if($student->enrollments->isEmpty())
                                <span class="pill pill-grey">Not enrolled yet</span>
                            @else
                                <div class="flex flex-wrap gap-1">
                                    @foreach($student->enrollments->take(3) as $e)
                                        @php
                                            $label = $e->completed_at ? 'Completed' : ($e->expires_at && $e->expires_at->isPast() ? 'Expired' : ((int) $e->progress_percent === 0 ? 'Not started' : 'In progress'));
                                            $pillClass = match(true) {
                                                (bool) $e->completed_at => '',
                                                $e->expires_at && $e->expires_at->isPast() => 'pill-warn',
                                                default => 'pill-grey',
                                            };
                                        @endphp
                                        <span class="pill {{ $pillClass }}" title="{{ $e->course->title }}: {{ $label }}">{{ Str::limit($e->course->title, 20) }}</span>
                                    @endforeach
                                    @if($student->enrollments->count() > 3)
                                        <span class="muted text-xs">+{{ $student->enrollments->count() - 3 }} more</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="tabular-nums">{{ $student->enrollments_count }}</td>
                        <td class="tabular-nums">{{ $student->completed_count }}</td>
                        <td class="text-right"><a href="{{ route('business.people.edit', $student) }}" class="text-sm font-medium underline">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted !py-10 text-center">No students match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $students->links() }}</div>
</div>
</x-panel-layout>
