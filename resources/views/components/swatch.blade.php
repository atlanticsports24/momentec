@props([
    'color' => null,
    'hex' => '#CCCCCC',
    'name' => '',
    'active' => false,
    'size' => 'md',
])

@php
    $sizeClass = match($size) {
        'sm' => 'h-4 w-4',
        'lg' => 'h-8 w-8',
        default => 'h-6 w-6',
    };
@endphp

<button
    type="button"
    title="{{ $name ?: $color }}"
    {{ $attributes->merge([
        'class' => "{$sizeClass} rounded-full border-2 transition " . ($active ? 'border-accent ring-2 ring-accent ring-offset-1' : 'border-gray-300 hover:border-accent'),
        'style' => "background-color: {$hex}",
        'aria-label' => $name ?: $color,
    ]) }}
></button>
