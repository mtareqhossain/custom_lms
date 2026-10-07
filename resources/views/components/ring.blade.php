@props(['percent' => 0, 'size' => 56])
@php
    $r = 20;
    $c = round(2 * M_PI * $r, 2);
    $offset = round($c * (1 - max(0, min(100, $percent)) / 100), 2);
@endphp
<div class="relative shrink-0" style="width: {{ $size }}px; height: {{ $size }}px" role="img" aria-label="{{ $percent }}% complete">
    <svg viewBox="0 0 48 48" width="{{ $size }}" height="{{ $size }}" style="transform: rotate(-90deg)">
        <circle class="ring-bg" cx="24" cy="24" r="{{ $r }}" fill="none" stroke-width="5"/>
        <circle class="ring-fg" cx="24" cy="24" r="{{ $r }}" fill="none" stroke-width="5" stroke-linecap="round"
                stroke-dasharray="{{ $c }}" stroke-dashoffset="{{ $offset }}" style="--c: {{ $c }}"/>
    </svg>
    <span class="absolute inset-0 grid place-items-center text-xs font-semibold">{{ $percent }}%</span>
</div>
