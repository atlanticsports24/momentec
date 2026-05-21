@extends('layouts.app')

@section('title', $product->name)
@section('meta_description', Str::limit(strip_tags($product->description ?? ''), 155))

@section('og_tags')
    <meta property="og:type" content="product">
    <meta property="og:title" content="{{ $product->name }} | Momentec">
    <meta property="og:description" content="{{ Str::limit(strip_tags($product->description ?? ''), 155) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($product->mainImageUrl())
        <meta property="og:image" content="{{ $product->mainImageUrl() }}">
    @endif
@endsection

@section('schema_json')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Product",
    "name": @json($product->name),
    "sku": @json($product->parent_sku),
    "description": @json(Str::limit(strip_tags($product->description ?? ''), 500)),
    "image": @json($product->images->map(fn ($img) => $img->publicUrl())->filter()->values()),
    "brand": {
        "@@type": "Brand",
        "name": @json($product->brand?->name)
    },
    "offers": {
        "@@type": "AggregateOffer",
        "lowPrice": "{{ $product->min_msrp }}",
        "highPrice": "{{ $product->max_msrp }}",
        "priceCurrency": "{{ $product->currency ?? 'USD' }}",
        "availability": "https://schema.org/InStock"
    }
}
</script>
@endsection

@push('styles')
<style>
    .pd-page { max-width: 1280px; margin: 0 auto; padding: 0 24px; }

    .breadcrumb { display: flex; align-items: center; gap: 8px; padding: 14px 0; font-size: 13px; color: #6b7280; flex-wrap: wrap; }
    .breadcrumb a { color: #6b7280; text-decoration: none; }
    .breadcrumb a:hover { color: #4f46e5; }
    .breadcrumb-sep { color: #d1d5db; }
    .breadcrumb-current { color: #111827; font-weight: 600; }

    .pd-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 48px;
        align-items: start;
    }
    @media (max-width: 768px) {
        .pd-layout { grid-template-columns: 1fr; gap: 32px; }
    }

    .pd-gallery { position: sticky; top: 80px; }
    .pd-main-img-wrap {
        aspect-ratio: 1 / 1;
        border-radius: 20px;
        overflow: hidden;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        margin-bottom: 12px;
    }
    .pd-main-img { width: 100%; height: 100%; object-fit: contain; }
    .pd-thumbs { display: flex; gap: 10px; flex-wrap: wrap; }
    .pd-thumb {
        width: 72px;
        height: 72px;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #e5e7eb;
        cursor: pointer;
        transition: border-color .2s;
        background: #f8fafc;
        padding: 0;
    }
    .pd-thumb.active,
    .pd-thumb:hover { border-color: #4f46e5; }
    .pd-thumb img { width: 100%; height: 100%; object-fit: contain; display: block; }

    .pd-info { padding: 8px 0; }
    .pd-brand {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #4f46e5;
        text-decoration: none;
        display: inline-block;
    }
    .pd-brand:hover { color: #3730a3; }
    .pd-title {
        font-size: clamp(1.4rem, 3vw, 1.9rem);
        font-weight: 800;
        color: #111827;
        line-height: 1.25;
        margin: 8px 0 12px;
    }
    .pd-sku { font-size: 12px; color: #9ca3af; margin-bottom: 16px; }
    .pd-price { font-size: 2rem; font-weight: 900; color: #111827; margin-bottom: 20px; }
    .pd-price-range { font-size: 1.1rem; font-weight: 500; color: #6b7280; }
    .pd-divider { height: 1px; background: #f1f5f9; margin: 20px 0; }
    .pd-label { font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 10px; }
    .pd-label span { font-weight: 500; color: #9ca3af; }
    .pd-categories { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
    .pd-category-tag {
        font-size: 12px;
        font-weight: 600;
        color: #4f46e5;
        background: #eef2ff;
        padding: 4px 12px;
        border-radius: 999px;
        text-decoration: none;
    }
    .pd-category-tag:hover { background: #e0e7ff; }
    .pd-swatches { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
    .pd-swatch {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid #e5e7eb;
        cursor: pointer;
        transition: transform .2s, border-color .2s, box-shadow .2s;
        padding: 0;
    }
    .pd-swatch:hover { transform: scale(1.15); }
    .pd-swatch.active { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, .2); }
    .pd-sizes { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; }
    .pd-size {
        padding: 8px 18px;
        border-radius: 10px;
        border: 1.5px solid #e5e7eb;
        background: #fff;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        transition: all .2s;
    }
    .pd-size:hover:not(:disabled) { border-color: #4f46e5; color: #4f46e5; }
    .pd-size.active { background: #4f46e5; color: #fff; border-color: #4f46e5; }
    .pd-size:disabled {
        cursor: not-allowed;
        color: #9ca3af;
        text-decoration: line-through;
        opacity: .6;
    }
    .pd-size-chart-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 700;
        color: #4f46e5;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        margin-bottom: 20px;
    }
    .pd-size-chart-btn:hover { color: #3730a3; }
    .pd-variant-box {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 24px;
        font-size: 13px;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    @media (min-width: 640px) {
        .pd-variant-box { grid-template-columns: repeat(3, 1fr); }
    }
    .pd-variant-row-label { color: #9ca3af; font-weight: 500; display: block; margin-bottom: 2px; }
    .pd-variant-row-val { color: #111827; font-weight: 700; }
    .pd-actions { display: flex; gap: 12px; margin-bottom: 24px; }
    .pd-btn-primary {
        flex: 1;
        background: #4f46e5;
        color: #fff;
        border: none;
        border-radius: 14px;
        padding: 15px 28px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: background .2s, transform .15s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
    }
    .pd-btn-primary:hover { background: #4338ca; transform: translateY(-1px); }
    .pd-btn-share {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        border: 1.5px solid #e5e7eb;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: border-color .2s, color .2s;
        color: #6b7280;
        flex-shrink: 0;
    }
    .pd-btn-share:hover { border-color: #4f46e5; color: #4f46e5; }

    .pd-tabs { margin-top: 48px; border-top: 1px solid #e5e7eb; padding-top: 32px; }
    .pd-tab-list { display: flex; gap: 0; border-bottom: 2px solid #e5e7eb; margin-bottom: 28px; overflow-x: auto; }
    .pd-tab-btn {
        padding: 12px 24px;
        font-size: 14px;
        font-weight: 600;
        color: #6b7280;
        border: none;
        background: none;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        transition: color .2s, border-color .2s;
        white-space: nowrap;
    }
    .pd-tab-btn.active { color: #4f46e5; border-bottom-color: #4f46e5; }
    .pd-tab-btn:hover { color: #374151; }
    .pd-tab-panel { display: none; }
    .pd-tab-panel.active { display: block; }
    .pd-desc { font-size: 14px; line-height: 1.7; color: #374151; }
    .pd-desc p { margin: 0 0 12px; }
    .pd-features { list-style: none; padding: 0; margin: 20px 0 0; display: grid; gap: 10px; }
    @media (min-width: 640px) {
        .pd-features { grid-template-columns: repeat(2, 1fr); }
    }
    .pd-features li {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 14px;
        color: #374151;
    }
    .pd-features svg { width: 18px; height: 18px; flex-shrink: 0; color: #059669; margin-top: 2px; }
    .pd-table-wrap { overflow-x: auto; border: 1px solid #e5e7eb; border-radius: 14px; }
    .pd-table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 640px; }
    .pd-table th {
        background: #f8fafc;
        padding: 12px 16px;
        text-align: left;
        font-weight: 700;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .pd-table th button {
        background: none;
        border: none;
        cursor: pointer;
        font: inherit;
        color: inherit;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 0;
    }
    .pd-table th button:hover { color: #4f46e5; }
    .pd-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; color: #374151; }
    .pd-table tr:hover td { background: #fafafa; }
    .pd-table tr.row-selected td { background: #eef2ff; }
    .pd-table-sort { color: #4f46e5; }
    .pd-status-pill {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 999px;
        background: #f1f5f9;
        font-size: 11px;
        font-weight: 600;
    }
    .pd-img-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
    @media (max-width: 640px) {
        .pd-img-grid { grid-template-columns: repeat(2, 1fr); }
    }
    .pd-img-grid-item {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        background: #f8fafc;
        cursor: pointer;
        padding: 0;
        text-align: left;
        transition: border-color .2s, box-shadow .2s;
        display: flex;
        flex-direction: column;
    }
    .pd-img-grid-item:hover { border-color: #4f46e5; box-shadow: 0 4px 12px rgba(0, 0, 0, .08); }
    .pd-img-grid-figure { aspect-ratio: 1 / 1; overflow: hidden; }
    .pd-img-grid-item img { width: 100%; height: 100%; object-fit: contain; display: block; }
    .pd-img-grid-cap {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        color: #6b7280;
        padding: 6px 8px;
        text-align: center;
    }

    .pd-related { margin-top: 48px; padding-top: 32px; border-top: 1px solid #e5e7eb; }
    .pd-related-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; gap: 16px; }
    .pd-related-title { font-size: 1.3rem; font-weight: 800; color: #111827; margin: 0; }
    .pd-related-link { font-size: 13px; font-weight: 700; color: #4f46e5; text-decoration: none; }
    .pd-related-link:hover { color: #3730a3; }
    .pd-related-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
    @media (min-width: 768px) {
        .pd-related-grid { grid-template-columns: repeat(4, 1fr); }
    }

    .pd-modal {
        position: fixed;
        inset: 0;
        z-index: 70;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .pd-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, .6);
    }
    .pd-modal-panel {
        position: relative;
        max-height: 90vh;
        max-width: 48rem;
        width: 100%;
        overflow: auto;
        border-radius: 16px;
        background: #fff;
        padding: 20px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, .2);
    }
    .pd-modal-close {
        position: absolute;
        right: 12px;
        top: 12px;
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 8px;
        background: #f8fafc;
        color: #6b7280;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .pd-modal-close:hover { background: #f1f5f9; color: #111827; }
    .pd-modal-title { font-size: 1.125rem; font-weight: 800; color: #111827; margin: 0 0 16px; }
    .pd-modal-img { width: 100%; border-radius: 12px; display: block; }

    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
@php
    $placeholder = asset('images/placeholder.jpg');
    $defaultImage = $product->mainImageUrl() ?? $placeholder;

    $galleryImages = $product->images
        ->map(fn ($image) => [
            'url' => $image->publicUrl(),
            'role' => $image->role,
            'alt' => $product->name . ' — ' . ucfirst(str_replace('_', ' ', $image->role ?? 'image')),
        ])
        ->filter(fn ($img) => filled($img['url']))
        ->unique('url')
        ->values();

    if ($galleryImages->isEmpty()) {
        $galleryImages = collect([['url' => $defaultImage, 'role' => 'main', 'alt' => $product->name]]);
    }

    $sizeChartImage = $product->images->first(fn ($img) => $img->role === 'size_chart' && $img->publicUrl());
    $sizeChartUrl = $sizeChartImage?->publicUrl();

    $variantsForJs = $product->variants->map(fn ($variant) => [
        'id' => $variant->id,
        'item_sku' => $variant->item_sku,
        'color' => $variant->color,
        'size' => $variant->size,
        'msrp' => $variant->msrp ? (float) $variant->msrp : null,
        'upc_code' => $variant->upc_code,
        'weight' => $variant->weight ? (float) $variant->weight : null,
        'weight_unit' => $variant->weight_unit,
        'country_of_origin' => $variant->country_of_origin,
        'status' => $variant->status,
        'color_hex_value' => $variant->color_hex_value,
        'main_image_url' => $variant->mainImageUrl() ?? (filled($variant->main_image_url) && str_starts_with($variant->main_image_url, 'http') ? $variant->main_image_url : null),
    ])->values();

    $uniqueColors = $product->variants
        ->filter(fn ($v) => filled($v->color))
        ->unique('color')
        ->values();

    $primaryCategory = $product->categories->first();
    $breadcrumbCategory = $primaryCategory?->parent ?? $primaryCategory;

    $featureLines = collect(preg_split('/\r\n|\r|\n/', $product->features ?? ''))
        ->map(fn ($line) => trim($line))
        ->filter();

    $hasSizes = $product->variants->whereNotNull('size')->where('size', '!=', '')->isNotEmpty();
    $hasVariantsTab = $product->variants->count() > 0;
    $hasImagesTab = $galleryImages->count() > 0;
    $hasDescriptionTab = filled($product->description) || $featureLines->isNotEmpty();
@endphp

<div class="pd-page" x-data="productDetailState">
    {{-- SECTION 1 — Breadcrumb --}}
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Home</a>
        <span class="breadcrumb-sep" aria-hidden="true">/</span>
        @if($breadcrumbCategory)
            <a href="{{ route('categories.show', $breadcrumbCategory) }}">{{ $breadcrumbCategory->name }}</a>
            <span class="breadcrumb-sep" aria-hidden="true">/</span>
        @else
            <a href="{{ route('products.index') }}">Products</a>
            <span class="breadcrumb-sep" aria-hidden="true">/</span>
        @endif
        <span class="breadcrumb-current" aria-current="page">{{ $product->name }}</span>
    </nav>

    {{-- SECTION 2 — Main Product Layout --}}
    <div class="pd-layout">
        {{-- LEFT — Image Gallery --}}
        <div class="pd-gallery">
            <div class="pd-main-img-wrap">
                <img
                    class="pd-main-img"
                    :src="displayImage"
                    alt="{{ $product->name }}"
                    width="600"
                    height="600"
                >
            </div>
            @if($galleryImages->count() > 1)
                <div class="pd-thumbs">
                    @foreach($galleryImages as $image)
                        <button
                            type="button"
                            class="pd-thumb"
                            :class="{ active: activeImageUrl === @js($image['url']) }"
                            @click="selectGalleryImage(@js($image['url']))"
                            aria-label="View {{ $image['alt'] }}"
                        >
                            <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" width="72" height="72">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- RIGHT — Product Info --}}
        <div class="pd-info">
            @if($product->brand)
                <a href="{{ route('brands.show', $product->brand) }}" class="pd-brand">{{ $product->brand->name }}</a>
            @endif

            <h1 class="pd-title">{{ $product->name }}</h1>

            <p class="pd-sku">SKU: <strong>{{ $product->parent_sku }}</strong></p>

            @if($product->min_msrp)
                <div class="pd-price">
                    From ${{ number_format($product->min_msrp, 2) }}
                    @if($product->max_msrp && $product->max_msrp > $product->min_msrp)
                        <span class="pd-price-range"> — ${{ number_format($product->max_msrp, 2) }}</span>
                    @endif
                </div>
            @endif

            @if($product->categories->isNotEmpty())
                <div class="pd-categories">
                    @foreach($product->categories->take(3) as $cat)
                        <a href="{{ route('categories.show', $cat) }}" class="pd-category-tag">{{ $cat->name }}</a>
                    @endforeach
                </div>
            @endif

            <div class="pd-divider"></div>

            @if($uniqueColors->isNotEmpty())
                <p class="pd-label">
                    Color
                    <span x-show="selectedColor" x-text="' — ' + selectedColor"></span>
                </p>
                <div class="pd-swatches">
                    @foreach($uniqueColors as $variant)
                        <button
                            type="button"
                            class="pd-swatch"
                            :class="{ active: selectedColor === @js($variant->color) }"
                            @click="selectColor(@js($variant->color))"
                            style="background-color: {{ $variant->color_hex_value ?: '#d1d5db' }}"
                            title="{{ $variant->color }}"
                            aria-label="Color {{ $variant->color }}"
                        ></button>
                    @endforeach
                </div>
            @endif

            @if($hasSizes)
                <p class="pd-label">
                    Size
                    <span x-show="selectedSize" x-text="' — ' + selectedSize"></span>
                </p>
                <div class="pd-sizes">
                    <template x-for="size in availableSizes" :key="size">
                        <button
                            type="button"
                            class="pd-size"
                            :class="{ active: selectedSize === size }"
                            :disabled="!isSizeAvailable(size)"
                            @click="selectSize(size)"
                            x-text="size"
                        ></button>
                    </template>
                </div>
            @endif

            @if($sizeChartUrl)
                <button type="button" class="pd-size-chart-btn" @click="sizeChartOpen = true">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0v10"/>
                    </svg>
                    View size chart
                </button>
            @endif

            <div class="pd-variant-box" x-show="selectedVariant" x-cloak>
                <div>
                    <span class="pd-variant-row-label">SKU</span>
                    <span class="pd-variant-row-val" x-text="selectedVariant?.item_sku || '—'"></span>
                </div>
                <div>
                    <span class="pd-variant-row-label">Price</span>
                    <span class="pd-variant-row-val" x-text="selectedVariant?.msrp ? '$' + Number(selectedVariant.msrp).toFixed(2) : '—'"></span>
                </div>
                <div>
                    <span class="pd-variant-row-label">UPC</span>
                    <span class="pd-variant-row-val" x-text="selectedVariant?.upc_code || '—'"></span>
                </div>
                <div>
                    <span class="pd-variant-row-label">Weight</span>
                    <span class="pd-variant-row-val" x-text="selectedVariant?.weight ? selectedVariant.weight + ' ' + (selectedVariant.weight_unit || '') : '—'"></span>
                </div>
                <div>
                    <span class="pd-variant-row-label">Origin</span>
                    <span class="pd-variant-row-val" x-text="selectedVariant?.country_of_origin || '—'"></span>
                </div>
                <div>
                    <span class="pd-variant-row-label">Status</span>
                    <span class="pd-variant-row-val" x-text="selectedVariant?.status || 'Available'"></span>
                </div>
            </div>

            <div class="pd-actions">
                <a href="{{ route('products.index') }}" class="pd-btn-primary">
                    Browse Catalog
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
                <button type="button" class="pd-btn-share" @click="shareProduct()" aria-label="Share product" title="Share">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Size chart modal --}}
    @if($sizeChartUrl)
        <div
            x-show="sizeChartOpen"
            x-cloak
            class="pd-modal"
            role="dialog"
            aria-modal="true"
            aria-label="Size chart"
        >
            <div class="pd-modal-backdrop" @click="sizeChartOpen = false"></div>
            <div class="pd-modal-panel" @click.stop>
                <button type="button" class="pd-modal-close" @click="sizeChartOpen = false" aria-label="Close">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <h2 class="pd-modal-title">Size Chart</h2>
                <img class="pd-modal-img" :src="sizeChartUrl" alt="Size chart for {{ $product->name }}">
            </div>
        </div>
    @endif

    {{-- SECTION 3 — Product Tabs --}}
    @if($hasDescriptionTab || $hasVariantsTab || $hasImagesTab)
        <div class="pd-tabs">
            <div class="pd-tab-list" role="tablist">
                @if($hasDescriptionTab)
                    <button
                        type="button"
                        class="pd-tab-btn"
                        :class="{ active: activeTab === 'description' }"
                        @click="activeTab = 'description'"
                        role="tab"
                    >Description</button>
                @endif
                @if($hasVariantsTab)
                    <button
                        type="button"
                        class="pd-tab-btn"
                        :class="{ active: activeTab === 'variants' }"
                        @click="activeTab = 'variants'"
                        role="tab"
                    >All Variants</button>
                @endif
                @if($hasImagesTab)
                    <button
                        type="button"
                        class="pd-tab-btn"
                        :class="{ active: activeTab === 'images' }"
                        @click="activeTab = 'images'"
                        role="tab"
                    >All Images</button>
                @endif
            </div>

            @if($hasDescriptionTab)
                <div class="pd-tab-panel" :class="{ active: activeTab === 'description' }" role="tabpanel">
                    @if(filled($product->description))
                        <div class="pd-desc">
                            {!! nl2br(e($product->description)) !!}
                        </div>
                    @endif
                    @if($featureLines->isNotEmpty())
                        <ul class="pd-features">
                            @foreach($featureLines as $feature)
                                <li>
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            @if($hasVariantsTab)
                <div class="pd-tab-panel" :class="{ active: activeTab === 'variants' }" role="tabpanel">
                    <div class="pd-table-wrap">
                        <table class="pd-table">
                            <thead>
                                <tr>
                                    @foreach(['item_sku' => 'SKU', 'color' => 'Color', 'size' => 'Size', 'msrp' => 'Price', 'upc_code' => 'UPC', 'status' => 'Status'] as $key => $label)
                                        <th>
                                            <button type="button" @click="toggleSort('{{ $key }}')">
                                                {{ $label }}
                                                <span class="pd-table-sort" x-show="sortKey === '{{ $key }}'" x-text="sortDir === 'asc' ? '↑' : '↓'"></span>
                                            </button>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="variant in sortedVariants" :key="variant.id">
                                    <tr :class="{ 'row-selected': selectedVariant && selectedVariant.id === variant.id }">
                                        <td x-text="variant.item_sku || '—'"></td>
                                        <td x-text="variant.color || '—'"></td>
                                        <td x-text="variant.size || '—'"></td>
                                        <td x-text="variant.msrp ? '$' + Number(variant.msrp).toFixed(2) : '—'"></td>
                                        <td x-text="variant.upc_code || '—'"></td>
                                        <td>
                                            <span class="pd-status-pill" x-text="variant.status || 'Active'"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($hasImagesTab)
                <div class="pd-tab-panel" :class="{ active: activeTab === 'images' }" role="tabpanel">
                    <div class="pd-img-grid">
                        @foreach($galleryImages as $image)
                            <button
                                type="button"
                                class="pd-img-grid-item"
                                @click="selectGalleryImage(@js($image['url'])); window.scrollTo({ top: 0, behavior: 'smooth' })"
                            >
                                <div class="pd-img-grid-figure">
                                    <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" width="200" height="200">
                                </div>
                                <span class="pd-img-grid-cap">{{ $image['role'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- SECTION 4 — Related Products --}}
    @if($related->count())
        <section class="pd-related">
            <div class="pd-related-head">
                <h2 class="pd-related-title">Related Products</h2>
                <a href="{{ route('products.index') }}" class="pd-related-link">View all →</a>
            </div>
            <div class="pd-related-grid">
                @foreach($related as $rp)
                    <x-product-card :product="$rp" />
                @endforeach
            </div>
        </section>
    @endif
</div>

@push('head')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('productDetailState', () => ({
        variants: @json($variantsForJs),
        galleryImages: @json($galleryImages),
        activeImageUrl: @json($galleryImages->first()['url'] ?? $defaultImage),
        selectedColor: null,
        selectedSize: null,
        activeTab: @json($hasDescriptionTab ? 'description' : ($hasVariantsTab ? 'variants' : 'images')),
        sizeChartOpen: false,
        sizeChartUrl: @json($sizeChartUrl),
        sortKey: 'item_sku',
        sortDir: 'asc',
        productName: @json($product->name),

        get availableSizes() {
            const sizes = !this.selectedColor
                ? this.variants.map(v => v.size).filter(Boolean)
                : this.variants.filter(v => v.color === this.selectedColor).map(v => v.size);
            return [...new Set(sizes)];
        },

        get selectedVariant() {
            return this.variants.find(v => v.color === this.selectedColor && v.size === this.selectedSize) || null;
        },

        get displayImage() {
            if (this.selectedVariant?.main_image_url) {
                return this.selectedVariant.main_image_url;
            }
            return this.activeImageUrl;
        },

        selectColor(color) {
            this.selectedColor = this.selectedColor === color ? null : color;
            this.selectedSize = null;
            if (this.selectedColor) {
                const match = this.variants.find(v => v.color === color && v.main_image_url);
                if (match?.main_image_url) {
                    this.activeImageUrl = match.main_image_url;
                }
            }
        },

        selectSize(size) {
            if (!this.isSizeAvailable(size)) return;
            this.selectedSize = this.selectedSize === size ? null : size;
        },

        isSizeAvailable(size) {
            const pool = this.selectedColor
                ? this.variants.filter(v => v.color === this.selectedColor)
                : this.variants;
            const variant = pool.find(v => v.size === size);
            if (!variant) return false;
            const ok = ['active', 'in stock', 'available', 'instock', 'in_stock'];
            return !variant.status || ok.includes(String(variant.status).toLowerCase());
        },

        selectGalleryImage(url) {
            this.activeImageUrl = url;
        },

        get sortedVariants() {
            const sorted = [...this.variants];
            sorted.sort((a, b) => {
                let av = a[this.sortKey] ?? '';
                let bv = b[this.sortKey] ?? '';
                if (this.sortKey === 'msrp') {
                    av = parseFloat(av) || 0;
                    bv = parseFloat(bv) || 0;
                } else {
                    av = String(av).toLowerCase();
                    bv = String(bv).toLowerCase();
                }
                if (av < bv) return this.sortDir === 'asc' ? -1 : 1;
                if (av > bv) return this.sortDir === 'asc' ? 1 : -1;
                return 0;
            });
            return sorted;
        },

        toggleSort(key) {
            if (this.sortKey === key) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortKey = key;
                this.sortDir = 'asc';
            }
        },

        async shareProduct() {
            const url = window.location.href;
            const data = { title: this.productName, text: this.productName, url };
            if (navigator.share) {
                try { await navigator.share(data); return; } catch (e) {}
            }
            await navigator.clipboard.writeText(url);
            alert('Link copied to clipboard!');
        },
    }));
});
</script>
@endpush
@endsection
