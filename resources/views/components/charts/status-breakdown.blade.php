@props(['data', 'total'])
@php
    $segments = [
        ['key' => 'completed', 'label' => 'Completed', 'color' => '#1baf7a'],
        ['key' => 'in_progress', 'label' => 'In progress', 'color' => '#2a78d6'],
        ['key' => 'not_started', 'label' => 'Not started', 'color' => '#eda100'],
        ['key' => 'expired', 'label' => 'Expired', 'color' => '#e34948'],
    ];
    $visible = collect($segments)->filter(fn ($s) => ($data[$s['key']] ?? 0) > 0)->values();
@endphp
<div {{ $attributes->merge(['class' => 'panel p-5']) }}>
    <p class="font-semibold">Enrollment status</p>
    <p class="muted text-sm">{{ $total }} {{ Str::plural('enrollment', $total) }} across your organization</p>

    @if($total === 0)
        <p class="muted mt-8 text-center text-sm">No enrollments yet.</p>
    @else
        <div class="mt-5 flex h-6 w-full gap-[2px] overflow-hidden rounded-full">
            @foreach($visible as $i => $s)
                @php($pct = $data[$s['key']] / $total * 100)
                <div
                    style="width: {{ $pct }}%; background: {{ $s['color'] }};"
                    class="{{ $i === 0 ? 'rounded-l-full' : '' }} {{ $i === $visible->count() - 1 ? 'rounded-r-full' : '' }}"
                >
                    <span class="sr-only">{{ $s['label'] }}: {{ $data[$s['key']] }} ({{ round($pct) }}%)</span>
                </div>
            @endforeach
        </div>

        <dl class="mt-5 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
            @foreach($segments as $s)
                <div class="flex items-center gap-2">
                    <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $s['color'] }};" aria-hidden="true"></span>
                    <dt class="muted flex-1">{{ $s['label'] }}</dt>
                    <dd class="font-medium" style="font-variant-numeric: tabular-nums;">{{ $data[$s['key']] ?? 0 }}</dd>
                </div>
            @endforeach
        </dl>
    @endif
</div>
