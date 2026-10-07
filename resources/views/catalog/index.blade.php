<x-lms-layout title="Courses">
<div class="mx-auto max-w-6xl px-4 py-10">
    <h1 class="display reveal text-3xl sm:text-4xl">Find a course</h1>

    <form method="GET" id="catalogue-form" class="reveal mt-6 flex flex-col gap-4">
        <input name="q" value="{{ request('q') }}" placeholder="Search by title or topic" class="field w-full" aria-label="Search courses">
        <div class="flex flex-wrap gap-2" role="group" aria-label="Filter by price">
            @php($pricing = request('pricing', ''))
            <button type="submit" name="pricing" value="" class="chip {{ $pricing === '' ? 'active' : '' }}">All prices</button>
            <button type="submit" name="pricing" value="free" class="chip {{ $pricing === 'free' ? 'active' : '' }}">Free</button>
            <button type="submit" name="pricing" value="paid" class="chip {{ $pricing === 'paid' ? 'active' : '' }}">Paid</button>
        </div>
        @if($categories->isNotEmpty())
            <div class="flex flex-wrap gap-2" role="group" aria-label="Filter by category">
                @php($cat = request('category', ''))
                <button type="submit" name="category" value="" class="chip {{ $cat === '' ? 'active' : '' }}">All categories</button>
                @foreach($categories as $c)
                    <button type="submit" name="category" value="{{ $c->slug }}" class="chip {{ $cat === $c->slug ? 'active' : '' }}">{{ $c->name }}</button>
                @endforeach
            </div>
        @endif
    </form>

    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($courses as $course)
            <a href="{{ route('courses.show', $course) }}" class="course-card reveal" style="transition-delay: {{ ($loop->index % 3) * 70 }}ms">
                <div class="thumb-wrap relative aspect-video">
                    @if($course->thumbnail)
                        <img src="{{ Storage::url($course->thumbnail) }}" alt="" class="h-full w-full object-cover">
                    @else
                        <div class="thumb-fallback aspect-video">{{ Str::of($course->title)->substr(0, 1) }}</div>
                    @endif
                    <span class="level-badge">{{ ucfirst($course->level) }}</span>
                </div>
                <div class="p-5">
                    <h2 class="display text-lg leading-snug">{{ $course->title }}</h2>
                    <p class="muted mt-1 line-clamp-2 text-sm">{{ $course->summary }}</p>
                    <div class="mt-4 flex items-center justify-between text-sm">
                        <span class="muted">{{ $course->lessons_count }} lessons</span>
                        <span class="pill">{{ $course->isFree() ? 'Free' : $course->currency.' '.number_format($course->price_cents / 100, 2) }}</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="panel reveal col-span-full p-10 text-center">
                <p class="display text-xl">No courses match your search</p>
                <p class="muted mt-2">Try a different term, or clear your filters to see everything.</p>
                <a href="{{ route('courses.index') }}" class="btn btn-quiet mt-5">Clear filters</a>
            </div>
        @endforelse
    </div>
    <div class="mt-8">{{ $courses->links() }}</div>
</div>
</x-lms-layout>
