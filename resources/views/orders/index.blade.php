<x-lms-layout title="Order history">
<div class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="display text-3xl">Order history</h1>
    <div class="mt-6 divide-y divide-[var(--line)] panel">
        @forelse($orders as $order)
            <div class="flex flex-wrap items-center justify-between gap-3 p-5">
                <div>
                    <p class="font-semibold">{{ $order->course->title }}</p>
                    <p class="muted text-sm">{{ $order->paid_at->format('j M Y') }}</p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="font-medium">{{ $order->currency }} {{ number_format($order->amount_cents / 100, 2) }}</span>
                    @if($order->receipt_url)
                        <a href="{{ $order->receipt_url }}" target="_blank" rel="noopener" class="btn btn-quiet !py-2">Receipt</a>
                    @endif
                </div>
            </div>
        @empty
            <p class="muted p-8 text-center">No paid orders yet.</p>
        @endforelse
    </div>
</div>
</x-lms-layout>
