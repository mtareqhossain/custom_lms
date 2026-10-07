<x-lms-layout :title="$course->title">
<div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 lg:grid-cols-[1fr_22rem]">
    <div class="reveal">
        <div class="hero-thumb aspect-[21/9]">
            @php($intro = app(\App\Services\VideoUrlResolver::class)->resolveProvider($course->intro_video_provider, $course->intro_video_ref))
            @if($intro)
                @if($intro['type'] === 'iframe')
                    <iframe src="{{ $intro['url'] }}" class="h-full w-full" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen title="Course intro"></iframe>
                @else
                    <video src="{{ $intro['url'] }}" class="h-full w-full object-cover" controls></video>
                @endif
            @elseif($course->thumbnail)
                <img src="{{ Storage::url($course->thumbnail) }}" alt="" class="h-full w-full object-cover">
            @else
                <div class="thumb-fallback h-full w-full">{{ Str::of($course->title)->substr(0, 1) }}</div>
            @endif
            <span class="level-badge">{{ ucfirst($course->level) }}</span>
        </div>

        <h1 class="display mt-6 text-3xl sm:text-4xl">{{ $course->title }}</h1>
        <p class="muted mt-3 max-w-2xl text-lg">{{ $course->summary }}</p>
        <div class="mt-3 flex items-center gap-2">
            @if($course->instructor->avatarUrl())
                <img src="{{ $course->instructor->avatarUrl() }}" alt="" class="h-7 w-7 rounded-full object-cover">
            @else
                <span class="avatar" style="width:1.75rem;height:1.75rem;font-size:.7rem;">{{ Str::of($course->instructor->name)->substr(0, 1) }}</span>
            @endif
            <p class="muted text-sm">Taught by {{ $course->instructor->name }}</p>
        </div>

        @if($course->description)
            <div class="prose-lms mt-8">{!! clean($course->description) !!}</div>
        @endif

        <h2 class="display mt-10 text-2xl">What you will learn</h2>
        <div class="mt-4 space-y-3">
            @foreach($course->sections as $section)
                <details class="accordion panel p-4" @if($loop->first) open @endif>
                    <summary class="font-semibold">
                        <span>{{ $section->title }}
                            <span class="muted ml-2 text-sm font-normal">{{ $section->lessons->count() }} lessons</span>
                        </span>
                        <svg class="chev h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <div class="acc-body">
                        <div class="acc-inner">
                            <ul class="mt-3 space-y-1 text-sm">
                                @foreach($section->lessons as $l)
                                    <li class="flex justify-between gap-3">
                                        <span>{{ $l->title }}</span>
                                        @if($l->is_preview)<span class="pill">Preview</span>@endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </details>
            @endforeach
        </div>
    </div>

    <aside class="reveal lg:sticky lg:top-6 lg:self-start" x-data="{ stuck: false }"
           x-init="const io = new IntersectionObserver(([e]) => stuck = !e.isIntersecting, { threshold: 1 }); io.observe($el)">
        <div class="enrol-card panel overflow-hidden" :class="{ stuck }">
            @if($course->thumbnail)
                <img src="{{ Storage::url($course->thumbnail) }}" alt="" class="aspect-video w-full object-cover">
            @endif
            <div class="p-5">
                <p class="display text-3xl">{{ $course->isFree() ? 'Free' : $course->currency.' '.number_format($course->price_cents / 100, 2) }}</p>
                <p class="muted mt-1 text-sm">{{ $course->access_days ? $course->access_days.' days of access' : 'Lifetime access' }}</p>

                <div class="mt-5">
                    @if($enrollment && $enrollment->isActive())
                        <a href="{{ route('learn.show', $course) }}" class="btn w-full">Continue learning</a>
                    @elseif($enrollment)
                        <p class="text-sm font-medium">Your access has expired.</p>
                    @elseif(auth()->check())
                        <form method="POST" action="{{ route('courses.enroll', $course) }}">@csrf
                            <button class="btn w-full">{{ $course->isFree() ? 'Start course' : 'Buy course' }}</button>
                        </form>
                    @else
                        <a href="{{ route('register') }}" class="btn w-full">Create account to start</a>
                        <p class="muted mt-3 text-center text-sm">Already registered? <a class="underline" href="{{ route('login') }}">Log in</a></p>
                    @endif
                </div>
            </div>
        </div>
    </aside>
</div>
</x-lms-layout>
