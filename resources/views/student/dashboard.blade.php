<x-panel-layout title="Dashboard">
<div class="mx-auto max-w-6xl px-4 py-8">
    <h1 class="display text-3xl">Welcome back, {{ Str::of($user->name)->before(' ') }}</h1>
    <p class="muted mt-1">Here is where you are with your learning.</p>

    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat label="In progress" :value="$stats['in_progress']" />
        <x-stat label="Completed" :value="$stats['completed']" />
        <x-stat label="Certificates" :value="$stats['certificates']" />
        <x-stat label="Lessons finished" :value="$stats['lessons']" />
    </div>

    @if($continue)
        <div class="panel mt-8 flex flex-col gap-5 p-6 sm:flex-row sm:items-center">
            <x-ring :percent="(int) $continue->progress_percent" :size="72" />
            <div class="min-w-0 flex-1">
                <p class="muted text-xs font-semibold uppercase tracking-wide">Continue learning</p>
                <h2 class="display mt-1 truncate text-xl">{{ $continue->course->title }}</h2>
                @if($nextLesson)<p class="muted mt-1 text-sm">Up next: {{ $nextLesson->title }}</p>@endif
            </div>
            <a href="{{ $nextLesson ? route('learn.show', [$continue->course, $nextLesson]) : route('learn.show', $continue->course) }}" class="btn">
                {{ (int) $continue->progress_percent > 0 ? 'Resume' : 'Start' }}
            </a>
        </div>
    @endif

    <div class="mt-10 flex items-end justify-between gap-3">
        <h2 class="display text-2xl">Your courses</h2>
        @if($enrollments->count() > 6)<a href="{{ route('my.courses') }}" class="text-sm font-medium underline">See all</a>@endif
    </div>

    @if($enrollments->isEmpty())
        <div class="panel mt-4 p-8 text-center">
            <p class="font-medium">You have not started any courses yet.</p>
            <a href="{{ route('courses.index') }}" class="btn mt-4">Browse courses</a>
        </div>
    @else
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($enrollments->take(6) as $e)
                <div class="panel flex flex-col p-5">
                    <div class="flex items-start gap-4">
                        <x-ring :percent="(int) $e->progress_percent" :size="52" />
                        <div class="min-w-0">
                            <h3 class="display leading-snug">{{ $e->course->title }}</h3>
                            <p class="muted mt-1 text-xs">
                                @if($e->completed_at) Completed {{ $e->completed_at->format('j M Y') }}
                                @elseif(!$e->isActive()) Access expired
                                @elseif($e->expires_at) Access ends {{ $e->expires_at->diffForHumans() }}
                                @else Lifetime access @endif
                            </p>
                        </div>
                    </div>
                    <div class="mt-auto flex items-center gap-2 pt-4">
                        @if($e->isActive())
                            <a href="{{ route('learn.show', $e->course) }}" class="btn !py-2 text-sm">{{ $e->completed_at ? 'Review' : 'Continue' }}</a>
                        @else
                            <a href="{{ route('courses.show', $e->course) }}" class="btn btn-quiet !py-2 text-sm">View course</a>
                        @endif
                        @if($e->certificate)<span class="pill">Certificate</span>@endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($suggested->isNotEmpty())
        <h2 class="display mt-10 text-2xl">Explore more</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($suggested as $course)
                <a href="{{ route('courses.show', $course) }}" class="panel block p-5 transition hover:border-[var(--teal)]">
                    <h3 class="display leading-snug">{{ $course->title }}</h3>
                    <p class="muted mt-1 line-clamp-2 text-sm">{{ $course->summary }}</p>
                    <div class="mt-4 flex items-center justify-between text-sm">
                        <span class="muted">{{ $course->lessons_count }} lessons</span>
                        <span class="pill">{{ $course->isFree() ? 'Free' : $course->currency.' '.number_format($course->price_cents / 100, 2) }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
</x-panel-layout>
