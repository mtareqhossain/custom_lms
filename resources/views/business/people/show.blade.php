<x-panel-layout title="Training history">
<div class="mx-auto max-w-4xl px-4 py-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="display text-3xl">{{ $student->name }}</h1>
            <p class="muted mt-1">{{ $student->email }}@if($student->employee_ref) &middot; {{ $student->employee_ref }}@endif</p>
        </div>
        <a href="{{ route('business.people.edit', $student) }}" class="btn btn-quiet">Edit</a>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-stat label="Courses" :value="$enrollments->count()" />
        <x-stat label="Completed" :value="$enrollments->whereNotNull('completed_at')->count()" />
        <x-stat label="Hours logged" :value="$hoursLogged.'h'" />
        <x-stat label="Manager" :value="$student->manager?->name ?? '—'" />
    </div>

    <h2 class="display mt-10 text-xl">Courses</h2>
    <div class="panel mt-3 divide-y divide-[var(--line)]">
        @forelse($enrollments as $e)
            <div class="flex items-center justify-between gap-3 p-4 text-sm">
                <div class="min-w-0">
                    <p class="truncate font-medium">{{ $e->course->title }}</p>
                    <p class="muted text-xs">Enrolled {{ $e->created_at->format('j M Y') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="bar w-28"><span style="width: {{ (int) $e->progress_percent }}%"></span></div>
                    <span class="muted w-10 text-right text-xs">{{ (int) $e->progress_percent }}%</span>
                    @if($e->certificate)<a href="{{ route('certificates.download', $e->certificate->code) }}" class="text-xs font-medium underline">Certificate</a>@endif
                </div>
            </div>
        @empty
            <p class="muted p-6 text-center text-sm">Not enrolled in anything yet.</p>
        @endforelse
    </div>

    <h2 class="display mt-10 text-xl">Assigned training</h2>
    <div class="panel mt-3 divide-y divide-[var(--line)]">
        @forelse($assignments as $a)
            <div class="flex items-center justify-between gap-3 p-4 text-sm">
                <div class="min-w-0"><p class="truncate font-medium">{{ $a->course->title }}</p><p class="muted text-xs">Due {{ $a->due_at?->format('j M Y') ?? 'No deadline' }}</p></div>
                @php($st = $a->status())
                <span class="pill {{ match($st) { 'completed' => '', 'overdue' => 'pill-warn', default => 'pill-grey' } }}">
                    {{ ['assigned' => 'Assigned', 'due_soon' => 'Due soon', 'overdue' => 'Overdue', 'completed' => 'Completed'][$st] }}
                </span>
            </div>
        @empty
            <p class="muted p-6 text-center text-sm">No training assigned yet.</p>
        @endforelse
    </div>
</div>
</x-panel-layout>
