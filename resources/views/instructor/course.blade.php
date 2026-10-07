<x-panel-layout :title="$course->title.' | Learners'">
@php($enrolled = $enrollments->count())
<div class="mx-auto max-w-6xl px-4 py-10">
    <a href="{{ route('instructor.dashboard') }}" class="muted text-sm">&larr; All courses</a>
    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="display text-3xl">{{ $course->title }}</h1>
        <a href="{{ route('instructor.export', $course) }}" class="btn btn-quiet">Export CSV</a>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat label="Learners" :value="$enrolled" />
        <x-stat label="Completed" :value="$enrollments->whereNotNull('completed_at')->count()" />
        <x-stat label="Average progress" :value="(int) round($enrollments->avg('progress_percent') ?? 0).'%'" />
        <x-stat label="Not started" :value="$enrollments->where('progress_percent', 0)->count()" />
    </div>

    <h2 class="display mt-10 text-xl">Where learners get to</h2>
    <p class="muted text-sm">Share of enrolled learners who completed each lesson. A big drop shows where people stall.</p>
    <div class="panel mt-4 space-y-4 p-5">
        @forelse($course->sections as $section)
            <div>
                <p class="muted text-xs font-semibold uppercase tracking-wide">{{ $section->title }}</p>
                <div class="mt-2 space-y-2">
                    @foreach($section->lessons as $lesson)
                        @php($n = $doneByLesson[$lesson->id] ?? 0)
                        @php($pct = $enrolled ? (int) round($n / $enrolled * 100) : 0)
                        <div class="grid grid-cols-[1fr_8rem_3rem] items-center gap-3 text-sm sm:grid-cols-[1fr_14rem_3rem]">
                            <span class="truncate">{{ $lesson->title }}</span>
                            <div class="bar"><span style="width: {{ $pct }}%"></span></div>
                            <span class="muted text-right">{{ $pct }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="muted text-sm">This course has no lessons yet.</p>
        @endforelse
    </div>

    <h2 class="display mt-10 text-xl">Learners</h2>
    <div class="panel mt-4 overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-[var(--line)] text-xs uppercase tracking-wide text-[var(--muted)]">
                <tr><th class="p-4">Learner</th><th class="p-4">Progress</th><th class="p-4">Last active</th><th class="p-4">Status</th></tr>
            </thead>
            <tbody class="divide-y divide-[var(--line)]">
                @forelse($enrollments as $e)
                    <tr>
                        <td class="p-4">
                            <p class="font-medium">{{ $e->user->name }}</p>
                            <p class="muted text-xs">{{ $e->user->email }}</p>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="bar w-28"><span style="width: {{ $e->progress_percent }}%"></span></div>
                                <span class="muted text-xs">{{ $e->progress_percent }}%</span>
                            </div>
                        </td>
                        <td class="muted p-4">{{ isset($lastActive[$e->id]) ? \Illuminate\Support\Carbon::parse($lastActive[$e->id])->diffForHumans() : 'Not started' }}</td>
                        <td class="p-4">
                            @if($e->completed_at)<span class="pill">Completed</span>
                            @elseif(!$e->isActive())<span class="muted">Expired</span>
                            @else<span class="muted">In progress</span>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td class="muted p-8 text-center" colspan="4">Nobody is enrolled yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-panel-layout>
