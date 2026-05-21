@props(['product'])

@php
    $mainImage = $product->mainImageUrl() ?? asset('images/placeholder.jpg');
    $hoverImage = $product->images
        ->filter(fn ($img) => $img->role !== 'main' && $img->publicUrl())
        ->sortBy('sort_order')
        ->first()
        ?->publicUrl();

    $colorVariants = $product->variants
        ->filter(fn ($v) => filled($v->color))
        ->unique('color')
        ->values();

    $displayColors = $colorVariants->take(5);
    $extraColorCount = max($colorVariants->count() - 5, 0);

    $ribbon = $product->variants->first(fn ($v) => filled($v->ribbon))?->ribbon;

    $sizes = $product->variants
        ->filter(fn ($v) => filled($v->size))
        ->pluck('size')
        ->unique()
        ->values();
@endphp

<article class="group relative flex h-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg">
    <a href="{{ route('products.show', $product) }}" class="flex flex-1 flex-col">
        <div class="relative aspect-square overflow-hidden bg-gray-100">
            <img
                src="{{ $mainImage }}"
                alt="{{ $product->name }}"
                loading="lazy"
                width="400"
                height="400"
                class="h-full w-full object-cover transition-opacity duration-500 {{ $hoverImage ? 'group-hover:opacity-0' : 'group-hover:scale-105' }}"
            >
            @if($hoverImage)
                <img
                    src="{{ $hoverImage }}"
                    alt="{{ $product->name }} — alternate view"
                    loading="lazy"
                    width="400"
                    height="400"
                    class="absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                >
            @endif

            @if($ribbon)
                <span class="absolute left-0 top-3 z-10 rounded-r-lg bg-accent px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-white shadow">
                    {{ $ribbon }}
                </span>
            @endif

            <div class="absolute inset-x-0 bottom-0 flex translate-y-full justify-center p-3 transition-transform duration-300 group-hover:translate-y-0">
                <span class="rounded-lg bg-white/95 px-4 py-2 text-xs font-semibold text-primary shadow-md backdrop-blur-sm">
                    View Product
                </span>
            </div>
        </div>

        <div class="flex flex-1 flex-col p-4">
            @if($product->brand)
                <p class="text-xs font-semibold uppercase tracking-wide text-accent">{{ $product->brand->name }}</p>
            @endif

            <h3 class="mt-1 line-clamp-2 text-base font-semibold leading-snug text-primary group-hover:text-accent">
                {{ $product->name }}
            </h3>

            @if($product->min_msrp)
                <p class="mt-2 text-lg font-bold text-primary">
                    From ${{ number_format($product->min_msrp, 2) }}
                    @if($product->max_msrp && $product->max_msrp > $product->min_msrp)
                        <span class="text-sm font-normal text-gray-400">– ${{ number_format($product->max_msrp, 2) }}</span>
                    @endif
                </p>
            @endif

            @if($displayColors->isNotEmpty())
                <div class="mt-3 flex flex-wrap items-center gap-1.5" aria-label="Available colors">
                    @foreach($displayColors as $variant)
                        <span
                            title="{{ $variant->color }}"
                            class="h-4 w-4 rounded-full border border-gray-200 shadow-sm"
                            style="background-color: {{ $variant->color_hex_value ?: '#d1d5db' }}"
                        ></span>
                    @endforeach
                    @if($extraColorCount > 0)
                        <x-badge variant="muted">+{{ $extraColorCount }}</x-badge>
                    @endif
                </div>
            @endif

            @if($sizes->isNotEmpty())
                <div class="mt-2 flex flex-wrap gap-1">
                    @foreach($sizes->take(6) as $size)
                        <span class="rounded border border-gray-200 bg-gray-50 px-1.5 py-0.5 text-[10px] font-medium text-gray-600">{{ $size }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </a>
</article>
