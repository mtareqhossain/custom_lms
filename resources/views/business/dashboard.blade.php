<x-panel-layout title="Overview">
<div class="mx-auto max-w-6xl px-4 py-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="display text-3xl">Team overview</h1>
            <p class="muted mt-1">{{ auth()->user()->organization?->name }}</p>
        </div>
        <a href="{{ route('business.import.create') }}" class="btn"><x-lms-icon name="upload" class="h-4 w-4" /> Import students</a>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
        <x-stat label="Students" :value="$stats['students']" />
        <x-stat label="Enrollments" :value="$stats['enrollments']" />
        <x-stat label="Completion rate" :value="$stats['completion_rate'].'%'" />
        <x-stat label="Active this week" :value="$stats['active_week']" />
        <x-stat label="Certificates" :value="$stats['certificates']" class="col-span-2 lg:col-span-1" />
    </div>

    <h2 class="display mt-10 text-xl">Progress by course</h2>
    <div class="mt-3 space-y-3">
        @forelse($courses as $course)
            @php($pct = $course->enrolled_count ? (int) round($course->completed_count / $course->enrolled_count * 100) : 0)
            @php($seatsFull = $course->seat_limit && $course->enrolled_count >= $course->seat_limit)
            <a href="{{ route('business.course', $course) }}" class="panel flex flex-col gap-3 p-5 transition hover:border-[var(--teal)] sm:flex-row sm:items-center">
                <div class="min-w-0 flex-1">
                    <p class="font-semibold">{{ $course->title }}</p>
                    <p class="muted text-sm">
                        {{ $course->enrolled_count }} enrolled, {{ $course->completed_count }} completed
                        &middot;
                        @if($course->seat_limit)
                            <span class="{{ $seatsFull ? 'font-medium' : '' }}" style="{{ $seatsFull ? 'color:#B45309;' : '' }}">
                                {{ $course->enrolled_count }} / {{ $course->seat_limit }} seats
                                @if($seatsFull) &mdash; full @endif
                            </span>
                        @else
                            unlimited seats
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-3 sm:w-60">
                    <div class="bar flex-1"><span style="width: {{ $pct }}%"></span></div>
                    <span class="muted w-10 text-right text-sm">{{ $pct }}%</span>
                </div>
            </a>
        @empty
            <div class="panel p-8 text-center">
                <p class="font-medium">No students enrolled yet.</p>
                <a href="{{ route('business.import.create') }}" class="btn mt-4">Import your first batch</a>
            </div>
        @endforelse
    </div>

    <div class="mt-10 grid gap-6 lg:grid-cols-2">
        <section>
            <h2 class="display text-xl">Not started after a week</h2>
            <div class="panel mt-3 divide-y divide-[var(--line)]">
                @forelse($notStarted as $e)
                    <div class="flex items-center justify-between gap-3 p-4 text-sm">
                        <div class="min-w-0"><p class="truncate font-medium">{{ $e->user->name }}</p><p class="muted truncate text-xs">{{ $e->course->title }}</p></div>
                        <span class="pill pill-grey shrink-0">Enrolled {{ $e->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="muted p-6 text-center text-sm">Everyone has made a start.</p>
                @endforelse
            </div>
        </section>

        <section>
            <h2 class="display text-xl">Access ending soon</h2>
            <div class="panel mt-3 divide-y divide-[var(--line)]">
                @forelse($expiring as $e)
                    <div class="flex items-center justify-between gap-3 p-4 text-sm">
                        <div class="min-w-0"><p class="truncate font-medium">{{ $e->user->name }}</p><p class="muted truncate text-xs">{{ $e->course->title }} &middot; {{ (int) $e->progress_percent }}% done</p></div>
                        <span class="pill pill-warn shrink-0">Ends {{ $e->expires_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="muted p-6 text-center text-sm">No access windows closing in the next 14 days.</p>
                @endforelse
            </div>
        </section>
    </div>

    <h2 class="display mt-10 text-xl">Recently completed</h2>
    <div class="panel mt-3 divide-y divide-[var(--line)]">
        @forelse($recent as $e)
            <div class="flex items-center justify-between gap-3 p-4 text-sm">
                <div class="min-w-0"><p class="truncate font-medium">{{ $e->user->name }}</p><p class="muted truncate text-xs">{{ $e->course->title }}</p></div>
                <span class="muted shrink-0">{{ $e->completed_at->format('j M Y') }}</span>
            </div>
        @empty
            <p class="muted p-6 text-center text-sm">Completions will show up here.</p>
        @endforelse
    </div>
</div>
</x-panel-layout>
