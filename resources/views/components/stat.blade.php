@props(['label', 'value'])
<div {{ $attributes->merge(['class' => 'panel p-5']) }}>
    <p class="muted text-sm">{{ $label }}</p>
    <p class="display mt-1 text-3xl">{{ $value }}</p>
</div>
