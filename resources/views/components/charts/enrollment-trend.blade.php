@props(['trend'])
@php
    $max = max(1, collect($trend)->max('count'));
    $barW = 28; $gap = 16; $chartH = 120; $topPad = 24; $bottomPad = 20;
    $width = count($trend) * ($barW + $gap) - $gap;
@endphp
<div {{ $attributes->merge(['class' => 'panel p-5']) }}>
    <p class="font-semibold">Enrollments, last 8 weeks</p>
    <p class="muted text-sm">New enrollments per week across your organization</p>

    <div class="mt-5 overflow-x-auto">
        <svg viewBox="0 0 {{ $width }} {{ $chartH + $topPad + $bottomPad }}" width="{{ $width }}" height="{{ $chartH + $topPad + $bottomPad }}" role="img" aria-label="Enrollments over the last 8 weeks">
            <line x1="0" y1="{{ $topPad + $chartH }}" x2="{{ $width }}" y2="{{ $topPad + $chartH }}" stroke="#D5DCE1" stroke-width="1" />
            @foreach($trend as $i => $point)
                @php
                    $h = $point['count'] > 0 ? max(4, ($point['count'] / $max) * ($chartH - 16)) : 0;
                    $x = $i * ($barW + $gap);
                    $y = $topPad + $chartH - $h;
                    $isLast = $i === count($trend) - 1;
                    $isMax = $point['count'] === $max && $max > 0;
                @endphp
                <g>
                    @if($h > 0)
                        <rect x="{{ $x }}" y="{{ $y }}" width="{{ $barW }}" height="{{ $h }}" rx="4" fill="#0B6E6E">
                            <title>{{ $point['label'] }}: {{ $point['count'] }} {{ Str::plural('enrollment', $point['count']) }}</title>
                        </rect>
                    @endif
                    @if($isLast || $isMax)
                        <text x="{{ $x + $barW / 2 }}" y="{{ $y - 6 }}" text-anchor="middle" font-size="11" fill="#10222B" font-weight="600">{{ $point['count'] }}</text>
                    @endif
                    <text x="{{ $x + $barW / 2 }}" y="{{ $topPad + $chartH + 16 }}" text-anchor="middle" font-size="10" fill="#5B6B75">{{ $point['label'] }}</text>
                </g>
            @endforeach
        </svg>
    </div>
</div>
