@props([
    'variant' => 'default',
])

@php
    $classes = match($variant) {
        'brand', 'success' => 'bg-brand-light text-brand',
        'sale' => 'bg-red-100 text-red-700',
        'new' => 'bg-accent-light text-accent',
        'muted' => 'bg-gray-100 text-gray-600',
        default => 'bg-accent-light text-accent',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {$classes}"]) }}>
    {{ $slot }}
</span>
