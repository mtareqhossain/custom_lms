<x-panel-layout title="My courses">
<div class="mx-auto max-w-4xl px-4 py-10">
    <h1 class="display text-3xl">My courses</h1>
    <div class="mt-6 space-y-4">
        @forelse($enrollments as $e)
            <div class="panel flex flex-col gap-4 p-5 sm:flex-row sm:items-center">
                <div class="flex-1">
                    <h2 class="display text-lg">{{ $e->course->title }}</h2>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-[var(--line)]" role="progressbar"
                         aria-valuenow="{{ $e->progress_percent }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="h-full bg-[var(--teal)]" style="width: {{ $e->progress_percent }}%"></div>
                    </div>
                    <p class="muted mt-2 text-sm">
                        {{ $e->progress_percent }}% complete
                        @if($e->expires_at)
                            , {{ $e->isActive() ? 'access ends '.$e->expires_at->diffForHumans() : 'access expired' }}
                        @endif
                    </p>
                </div>
                @if($e->isActive())
                    <a href="{{ route('learn.show', $e->course) }}" class="btn">{{ $e->progress_percent > 0 ? 'Continue' : 'Start' }}</a>
                @endif
                @if($e->certificate)<span class="pill">Certificate ready</span>@endif
            </div>
        @empty
            <div class="panel p-8 text-center">
                <p class="font-medium">You have not started any courses yet.</p>
                <a href="{{ route('courses.index') }}" class="btn mt-4">Browse courses</a>
            </div>
        @endforelse
    </div>
</div>
</x-panel-layout>
