<x-panel-layout title="Assignments">
<div class="mx-auto max-w-6xl px-4 py-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="display text-3xl">Assignments</h1>
            <p class="muted mt-1">Training you've assigned, with deadlines and recurrence.</p>
        </div>
        <a href="{{ route('business.assignments.create') }}" class="btn">Assign training</a>
    </div>

    @if(session('status'))
        <div class="mt-4 rounded-lg bg-[var(--mint)] px-4 py-3 text-sm font-medium text-[var(--teal-dark)]">{{ session('status') }}</div>
    @endif

    <form method="GET" class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-[14rem_11rem_auto]">
        <select name="course" class="field" aria-label="Course">
            <option value="">All courses</option>
            @foreach($courses as $c)<option value="{{ $c->id }}" @selected(request('course') == $c->id)>{{ $c->title }}</option>@endforeach
        </select>
        <select name="status" class="field" aria-label="Status">
            <option value="">Any status</option>
            @foreach(['assigned' => 'Assigned', 'due_soon' => 'Due soon', 'overdue' => 'Overdue', 'completed' => 'Completed'] as $val => $label)
                <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn">Filter</button>
    </form>

    <div class="panel mt-6 overflow-x-auto">
        <table class="tbl">
            <thead><tr><th>Learner</th><th>Course</th><th>Due</th><th>Recurs</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($assignments as $a)
                    <tr>
                        <td>
                            <p class="font-medium">{{ $a->user->name }}</p>
                            <p class="muted text-xs">{{ $a->user->email }}</p>
                        </td>
                        <td>{{ $a->course->title }}</td>
                        <td>{{ $a->due_at?->format('j M Y') ?? '—' }}</td>
                        <td>{{ \App\Models\TrainingAssignment::RECURRENCES[$a->recurrence] ?? '—' }}</td>
                        <td>
                            @php($s = $a->status())
                            <span class="pill {{ match($s) { 'completed' => '', 'overdue' => 'pill-warn', default => 'pill-grey' } }}">
                                {{ ['assigned' => 'Assigned', 'due_soon' => 'Due soon', 'overdue' => 'Overdue', 'completed' => 'Completed'][$s] }}
                            </span>
                        </td>
                        <td class="text-right">
                            @unless($a->completed_at)
                                <form method="POST" action="{{ route('business.assignments.destroy', $a) }}" onsubmit="return confirm('Remove this assignment?')">
                                    @csrf @method('DELETE')
                                    <button class="text-sm font-medium text-[var(--red,#e34948)] underline">Remove</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted !py-10 text-center">No assignments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $assignments->links() }}</div>
</div>
</x-panel-layout>
