<x-lms-layout :title="config('app.name').' | Training for teams and individuals'">

{{-- 1. HERO --}}
<section class="relative overflow-hidden">
    <div class="hero-photo">
        <img src="{{ $content->hero_image ? Storage::url($content->hero_image) : 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1600&q=60' }}" alt="">
    </div>
    <div class="relative mx-auto max-w-6xl px-4 py-24 sm:py-32">
        <div class="reveal max-w-2xl text-white">
            <span class="pill !bg-white/15 !text-white">Training platform for teams and individuals</span>
            <h1 class="display mt-5 text-4xl leading-tight sm:text-5xl">{{ $content->hero_headline }}</h1>
            <p class="mt-4 max-w-xl text-lg text-white/85">{{ $content->hero_subheadline }}</p>
            <div class="mt-7 flex flex-wrap gap-3">
                <a href="{{ route('courses.index') }}" class="btn !bg-white !text-[var(--teal-dark)] hover:!bg-[var(--mint)]">Browse courses</a>
                <a href="#for-business" class="btn btn-quiet !border-white/40 !text-white hover:!bg-white/10">For businesses</a>
            </div>
            <form method="GET" action="{{ route('courses.index') }}" class="hero-search mt-8 max-w-lg">
                <input name="q" placeholder="Search courses, e.g. &quot;first aid&quot;" aria-label="Search courses">
                <button class="btn !py-2">Search</button>
            </form>
        </div>
    </div>
</section>

{{-- 2. FOR BUSINESS TEAMS --}}
<section id="for-business" class="mx-auto max-w-6xl px-4 py-16">
    <div class="biz-panel reveal grid gap-10 p-8 sm:p-12 lg:grid-cols-[1.1fr_1fr] lg:items-center">
        <div>
            <span class="pill !bg-white/15 !text-white">For business customers</span>
            <h2 class="display mt-4 text-3xl sm:text-4xl">{{ $content->business_headline }}</h2>
            <p class="mt-3 text-white/80">{{ $content->business_subheadline }}</p>

            <div class="mt-7 grid gap-3 sm:grid-cols-2">
                @foreach($content->business_features as $feature)
                    <div class="biz-feature p-4">
                        <div class="flex items-center gap-2">
                            <x-lms-icon :name="$feature['icon']" class="h-5 w-5" />
                            <p class="font-semibold">{{ $feature['title'] }}</p>
                        </div>
                        <p class="mt-1 text-sm text-white/75">{{ $feature['text'] }}</p>
                    </div>
                @endforeach
            </div>

            <a href="mailto:{{ config('mail.from.address') }}?subject=Team training enquiry" class="btn mt-7 !bg-white !text-[var(--teal-dark)] hover:!bg-[var(--mint)]">Talk to us about team training</a>
        </div>

        <div class="grid grid-cols-3 gap-4 text-center" x-data="{ shown: false }" x-intersect.once="shown = true">
            @foreach([['organizations', 'Organizations trained'], ['certificates', 'Certificates issued'], ['courses', 'Courses available']] as [$key, $label])
                <div>
                    <p class="display count-up text-4xl" x-data="{ n: 0 }" x-init="$watch('shown', v => { if (!v) return; let target = {{ $stats[$key] }}, steps = 30; let i = 0;
                        let t = setInterval(() => { i++; n = Math.round(target * i / steps); if (i >= steps) { n = target; clearInterval(t); } }, 25); })" x-text="n"></p>
                    <p class="mt-1 text-sm text-white/75">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- 3. TOP CATEGORIES --}}
@if($categories->isNotEmpty())
<section class="mx-auto max-w-6xl px-4 py-12">
    <h2 class="display reveal text-3xl">Top categories</h2>
    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        @foreach($categories as $category)
            <a href="{{ route('courses.index', ['category' => $category->slug]) }}" class="cat-tile reveal" style="transition-delay: {{ $loop->index * 50 }}ms">
                <span class="cat-label">{{ $category->name }}<br><span class="font-normal text-white/80">{{ $category->courses_count }} courses</span></span>
            </a>
        @endforeach
    </div>
</section>
@endif

{{-- 4. TOP COURSES CAROUSEL --}}
@if($featured->isNotEmpty())
<section class="mx-auto max-w-6xl px-4 py-12">
    <div class="flex items-end justify-between gap-3">
        <h2 class="display reveal text-3xl">Top courses</h2>
        <a href="{{ route('courses.index') }}" class="text-sm font-medium underline">See all</a>
    </div>
    <div class="reveal mt-6 flex items-center gap-3">
        <button type="button" class="carousel-btn hidden sm:grid" onclick="document.getElementById('course-track').scrollBy({left:-300,behavior:'smooth'})" aria-label="Previous">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <div id="course-track" class="carousel-track flex-1">
            @foreach($featured as $course)
                <a href="{{ route('courses.show', $course) }}" class="course-card">
                    <div class="thumb-wrap relative aspect-video">
                        @if($course->thumbnail)
                            <img src="{{ Storage::url($course->thumbnail) }}" alt="" class="h-full w-full object-cover">
                        @else
                            <div class="thumb-fallback aspect-video">{{ Str::of($course->title)->substr(0, 1) }}</div>
                        @endif
                        <span class="level-badge">{{ ucfirst($course->level) }}</span>
                    </div>
                    <div class="p-4">
                        <h3 class="display text-base leading-snug">{{ Str::limit($course->title, 40) }}</h3>
                        <div class="mt-3 flex items-center justify-between text-sm">
                            <span class="muted">{{ $course->lessons_count }} lessons</span>
                            <span class="pill">{{ $course->isFree() ? 'Free' : $course->currency.' '.number_format($course->price_cents / 100, 2) }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <button type="button" class="carousel-btn hidden sm:grid" onclick="document.getElementById('course-track').scrollBy({left:300,behavior:'smooth'})" aria-label="Next">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        </button>
    </div>
</section>
@endif

{{-- 5. FEATURE STRIP --}}
<section class="mx-auto max-w-6xl px-4 py-12">
    <div class="grid gap-5 md:grid-cols-3">
        @foreach($content->feature_strip as $feature)
            <div class="panel reveal p-6" style="transition-delay: {{ $loop->index * 90 }}ms">
                <span class="grid h-11 w-11 place-items-center rounded-full bg-[var(--mint)] text-[var(--teal-dark)]"><x-lms-icon :name="$feature['icon']" class="h-5 w-5" /></span>
                <h3 class="display mt-4 text-lg">{{ $feature['title'] }}</h3>
                <p class="muted mt-2 text-sm">{{ $feature['text'] }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- 6. WHY US --}}
<section class="mx-auto max-w-6xl px-4 py-12">
    <h2 class="display reveal text-3xl">Why learn with us</h2>
    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-5">
        @foreach($content->why_us_points as $point)
            <div class="reveal" style="transition-delay: {{ $loop->index * 60 }}ms">
                <span class="grid h-8 w-8 place-items-center rounded-full bg-[var(--mint)] text-[var(--teal-dark)]">
                    <svg width="14" height="14" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 6.5l2.5 2.5L10 3.5"/></svg>
                </span>
                <p class="mt-3 text-sm font-medium">{{ $point }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- 7. INSTRUCTORS --}}
@if($instructors->isNotEmpty())
<section class="mx-auto max-w-6xl px-4 py-12">
    <h2 class="display reveal text-3xl">Meet a few of our instructors</h2>
    <div class="mt-6 grid grid-cols-2 gap-6 sm:grid-cols-4">
        @foreach($instructors as $instructor)
            <div class="instructor-card reveal" style="transition-delay: {{ $loop->index * 70 }}ms">
                @if($instructor->avatarUrl())
                    <img src="{{ $instructor->avatarUrl() }}" alt="" class="instructor-photo">
                @else
                    <div class="instructor-photo display flex items-center justify-center text-xl text-[var(--teal-dark)]">
                        {{ Str::of($instructor->name)->substr(0, 1) }}
                    </div>
                @endif
                <p class="mt-3 font-semibold">{{ $instructor->name }}</p>
                <p class="muted text-sm">{{ $instructor->taught_courses_count }} {{ Str::plural('course', $instructor->taught_courses_count) }}</p>
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- 8. BECOME AN INSTRUCTOR --}}
<section class="mx-auto max-w-6xl px-4 py-12">
    <div class="reveal panel grid gap-6 p-8 sm:p-10 lg:grid-cols-[1fr_auto] lg:items-center">
        <div>
            <h2 class="display text-2xl sm:text-3xl">{{ $content->instructor_cta_headline }}</h2>
            <p class="muted mt-2 max-w-xl">{{ $content->instructor_cta_text }}</p>
        </div>
        <a href="mailto:{{ config('mail.from.address') }}?subject=Instructor application" class="btn shrink-0">Apply to teach</a>
    </div>
</section>

{{-- 9. TRUSTED BY --}}
@if(count($content->client_logos))
<section class="mx-auto max-w-6xl px-4 py-12">
    <p class="muted reveal text-center text-sm font-semibold uppercase tracking-wide">Trusted by teams like yours</p>
    <div class="marquee-wrap reveal mt-6">
        <div class="marquee-track">
            @for($i = 0; $i < 2; $i++)
                @foreach($content->client_logos as $name)
                    <span class="logo-box">{{ $name }}</span>
                @endforeach
            @endfor
        </div>
    </div>
    {{-- <p class="muted mt-2 text-center text-xs">Placeholder names — replace with real client logos once available.</p> --}}
</section>
@endif

{{-- 10. TESTIMONIALS --}}
@if(count($content->testimonials))
<section class="mx-auto max-w-3xl px-4 py-12" x-data="{ i: 0, items: @js($content->testimonials) }">
    <h2 class="display reveal text-center text-3xl">What learners say</h2>
    <div class="reveal panel mt-6 p-8 text-center">
        <template x-for="(t, idx) in items" :key="idx">
            <div x-show="i === idx">
                <p class="text-lg italic" x-text="'&ldquo;' + t.quote + '&rdquo;'"></p>
                <p class="mt-4 font-semibold" x-text="t.name"></p>
                <p class="muted text-sm" x-text="t.role"></p>
            </div>
        </template>
        <div class="mt-6 flex justify-center gap-2">
            <template x-for="(t, idx) in items" :key="idx">
                <button type="button" class="dot-btn" :class="{ active: i === idx }" @click="i = idx" :aria-label="'Testimonial ' + (idx + 1)"></button>
            </template>
        </div>
    </div>
    {{-- <p class="muted mt-2 text-center text-xs">Placeholder quotes — replace with real testimonials once available.</p> --}}
</section>
@endif

{{-- 11. CERTIFICATIONS --}}
@if(count($content->certifications))
<section class="mx-auto max-w-6xl px-4 py-12">
    <p class="muted reveal text-center text-sm font-semibold uppercase tracking-wide">Recognized standards</p>
    <div class="marquee-wrap reveal mt-6">
        <div class="marquee-track">
            @for($i = 0; $i < 2; $i++)
                @foreach($content->certifications as $name)
                    <span class="logo-box">{{ $name }}</span>
                @endforeach
            @endfor
        </div>
    </div>
    {{-- <p class="muted mt-2 text-center text-xs">Placeholder — only display certifications you actually hold.</p> --}}
</section>
@endif

{{-- 12. FINAL CTA --}}
<section class="mx-auto max-w-6xl px-4 py-12">
    <div class="reveal rounded-2xl bg-[var(--teal)] p-8 text-center text-white sm:p-12">
        <h2 class="display text-3xl">{{ $content->final_cta_headline }}</h2>
        <p class="mx-auto mt-3 max-w-xl text-[var(--mint)]">{{ $content->final_cta_subheadline }}</p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="{{ route('register') }}" class="btn !bg-white !text-[var(--teal-dark)] hover:!bg-[var(--mint)]">Create a free account</a>
            <a href="mailto:{{ config('mail.from.address') }}" class="btn btn-quiet !border-white/40 !text-white hover:!bg-white/10">Contact us</a>
        </div>
    </div>
</section>
</x-lms-layout>
