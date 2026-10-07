<x-lms-layout :title="$lesson->title.' | '.$course->title">
<div class="mx-auto grid max-w-7xl gap-8 px-4 py-6 lg:grid-cols-[22rem_1fr]">

    <aside x-data="{ open: false, p: {{ $previousProgress / 100 }} }"
           x-init="$nextTick(() => p = {{ $enrollment->progress_percent / 100 }})">
        <button type="button" class="btn btn-quiet w-full justify-between lg:hidden" @click="open = !open" :aria-expanded="open">
            <span>Curriculum</span><span>{{ $enrollment->progress_percent }}% complete</span>
        </button>
        <div :class="open ? 'block' : 'hidden'" class="mt-3 lg:!block lg:mt-0">
            <p class="display hidden text-lg lg:block">{{ $course->title }}</p>
            <p class="muted mb-2 hidden text-sm lg:block">{{ $enrollment->progress_percent }}% complete</p>
            <nav class="track" :style="`--p:${p}`" aria-label="Course curriculum">
                @foreach($course->sections as $section)
                    <p class="track-section">{{ $section->title }}</p>
                    @foreach($section->lessons as $l)
                        @php($done = in_array($l->id, $completed))
                        @php($current = $l->id === $lesson->id)
                        <a href="{{ route('learn.show', [$course, $l]) }}" class="track-item {{ $current ? 'is-current' : '' }}"
                           @if($current) aria-current="page" @endif>
                            <span class="dot {{ $done ? 'done' : '' }} {{ $current ? 'current' : '' }}">
                                @if($done)
                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 6.5l2.5 2.5L10 3.5"/></svg>
                                @endif
                            </span>
                            {{ $l->title }}
                            @if($l->duration_seconds)<span class="muted ml-1 text-xs">{{ ceil($l->duration_seconds / 60) }} min</span>@endif
                        </a>
                    @endforeach
                @endforeach
            </nav>
        </div>
    </aside>

    <section class="lesson-fade" wire:key="{{ $lesson->id }}">
        <h1 class="display text-2xl sm:text-3xl">{{ $lesson->title }}</h1>

        @if($video)
            <div class="video-frame mt-5">
                @if($video['type'] === 'iframe')
                    <iframe src="{{ $video['url'] }}" class="aspect-video w-full" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen title="{{ $lesson->title }}"></iframe>
                @else
                    <video src="{{ $video['url'] }}" class="aspect-video w-full" controls controlsList="nodownload"></video>
                @endif
            </div>
        @endif

        @if($lesson->content)
            <div class="prose-lms mt-6">{!! clean($lesson->content) !!}</div>
        @endif

                @if($lesson->attachment)
            <a href="{{ Storage::url($lesson->attachment) }}" class="btn btn-quiet mt-6" download>Download lesson file</a>
        @endif

        @if($lesson->quizzes->isNotEmpty())
            <div class="mt-6 space-y-3">
                @foreach($lesson->quizzes as $quiz)
                    <x-quiz-card :quiz="$quiz" :user="auth()->user()" />
                @endforeach
            </div>
        @endif

        <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-[var(--line)] pt-6">
            @if($prev)<a href="{{ route('learn.show', [$course, $prev]) }}" class="btn btn-quiet">Previous</a>@endif
            <div class="flex-1"></div>
            @if($isDone)
                <span class="pill check-pop inline-flex items-center gap-1">
                    <svg width="14" height="14" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 6.5l2.5 2.5L10 3.5"/></svg>
                    Completed
                </span>
                                @if($next)
                    <a href="{{ route('learn.show', [$course, $next]) }}" class="btn">Next lesson</a>
                @elseif($course->finalQuizzes->isNotEmpty())
                    <div class="w-full mt-4 space-y-3">
                        @foreach($course->finalQuizzes as $quiz)
                            <x-quiz-card :quiz="$quiz" :user="auth()->user()" />
                        @endforeach
                    </div>
                @endif
            @else
                <form method="POST" action="{{ route('learn.complete', [$course, $lesson]) }}">@csrf
                    <button class="btn">{{ $next ? 'Complete and continue' : 'Complete course' }}</button>
                </form>
            @endif
        </div>
    </section>
</div>
</x-lms-layout>
