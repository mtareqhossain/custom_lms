{{-- resources/views/certificates/verify.blade.php --}}
<x-guest-layout>
<div class="mx-auto max-w-xl px-4 py-10">
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('home') }}" class="muted text-sm">&larr; Back</a>

    <div class="panel mt-4 p-8 text-center">
        <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-[var(--mint)] text-[var(--teal-dark)]">
            <x-lms-icon name="academic-cap" class="h-7 w-7" />
        </span>

        <h1 class="display mt-4 text-2xl">Certificate verified</h1>
        <p class="muted mt-1 text-sm">This certificate is valid and was issued by us.</p>

        <div class="mt-6 divide-y divide-[var(--line)] text-left">
            <div class="flex items-center justify-between py-3">
                <span class="muted text-sm">Awarded to</span>
                <span class="font-medium">{{ $certificate->enrollment->user->name }}</span>
            </div>
            <div class="flex items-center justify-between py-3">
                <span class="muted text-sm">Course</span>
                <span class="font-medium">{{ $certificate->enrollment->course->title }}</span>
            </div>
            <div class="flex items-center justify-between py-3">
                <span class="muted text-sm">Issued</span>
                <span class="font-medium">{{ $certificate->issued_at->format('j F Y') }}</span>
            </div>
            <div class="flex items-center justify-between py-3">
                <span class="muted text-sm">Certificate code</span>
                <span class="font-mono text-sm">{{ $certificate->code }}</span>
            </div>
        </div>

        <a href="{{ route('certificates.download', $certificate->code) }}" class="btn mt-6">Download PDF</a>
        <p class="muted mt-2 text-xs">Only the certificate holder or an administrator can download the PDF.</p>
    </div>
</div>
</x-guest-layout>
