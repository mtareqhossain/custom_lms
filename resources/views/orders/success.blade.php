<x-lms-layout title="Payment received">
<div class="mx-auto max-w-lg px-4 py-16 text-center">
    <div class="panel mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[var(--mint)]">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--teal-dark)" stroke-width="2"><path d="M4 12l5 5L20 6"/></svg>
    </div>
    <h1 class="display mt-6 text-2xl">Payment received</h1>
    <p class="muted mt-2">We're setting up your course access now, this usually takes a few seconds.</p>
    <a href="{{ route('my.courses') }}" class="btn mt-6">Go to my courses</a>
</div>
</x-lms-layout>
