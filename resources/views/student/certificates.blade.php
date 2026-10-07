<x-panel-layout title="Certificates">
<div class="mx-auto max-w-4xl px-4 py-8">
    <h1 class="display text-3xl">Your certificates</h1>
    <p class="muted mt-1">Every certificate has a public verification link you can share.</p>

    <div class="mt-6 space-y-4">
        @forelse($enrollments as $e)
            <div class="panel flex flex-col gap-4 p-5 sm:flex-row sm:items-center">
                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-[var(--mint)] text-[var(--teal-dark)]"><x-icon name="award" class="h-6 w-6" /></span>
                <div class="min-w-0 flex-1">
                    <h2 class="display text-lg">{{ $e->course->title }}</h2>
                    <p class="muted text-sm">Issued {{ $e->certificate->issued_at->format('j F Y') }} &middot; Code {{ Str::substr($e->certificate->code, 0, 8) }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('certificates.download', $e->certificate->code) }}" class="btn !py-2 text-sm">Download PDF</a>
                    <a href="{{ route('certificates.show', $e->certificate->code) }}" class="btn btn-quiet !py-2 text-sm">Verification page</a>
                </div>
            </div>
        @empty
            <div class="panel p-8 text-center">
                <p class="font-medium">No certificates yet.</p>
                <p class="muted mt-1 text-sm">Finish every lesson in a course and your certificate appears here.</p>
                <a href="{{ route('my.courses') }}" class="btn mt-4">Go to my courses</a>
            </div>
        @endforelse
    </div>
</div>
</x-panel-layout>
