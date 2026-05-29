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
    .pdp { max-width:1280px; margin:0 auto; padding:0 24px; }

    .bc { padding:12px 0; display:flex; align-items:center; gap:8px; font-size:12px; color:#9ca3af; flex-wrap:wrap; }
    .bc a { color:#9ca3af; text-decoration:none; }
    .bc a:hover { color:#4f46e5; }
    .bc-sep { color:#d1d5db; }

    .pdp-grid { display:grid; grid-template-columns:45% 55%; gap:40px; padding:24px 0 40px; align-items:start; }
    @media(max-width:768px) { .pdp-grid { grid-template-columns:1fr; } }

    .gal-main {
        aspect-ratio:1/1;
        border-radius:16px;
        overflow:hidden;
        background:#f8fafc;
        border:1.5px solid #e5e7eb;
        margin-bottom:12px;
        cursor:zoom-in;
        position:relative;
    }
    .gal-main img {
        width:100%;
        height:100%;
        object-fit:contain;
        transition:transform 0.4s ease;
        display:block;
    }
    .gal-main:hover img { transform:scale(1.08); }
    .gal-thumbs { display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
    .gal-thumb {
        width:68px;
        height:68px;
        border-radius:10px;
        overflow:hidden;
        border:2px solid #e5e7eb;
        cursor:pointer;
        background:#f8fafc;
        transition:border-color .2s, transform .15s;
        flex-shrink:0;
    }
    .gal-thumb:hover { border-color:#818cf8; transform:translateY(-2px); }
    .gal-thumb.on { border-color:#4f46e5; box-shadow:0 0 0 2px rgba(79,70,229,.2); }
    .gal-thumb img { width:100%; height:100%; object-fit:contain; display:block; }

    .pi-brand { font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.1em; color:#4f46e5; text-decoration:none; display:inline-block; margin-bottom:6px; }
    .pi-brand:hover { color:#3730a3; }
    .pi-title { font-size:clamp(1.3rem,2.5vw,1.8rem); font-weight:900; color:#111827; line-height:1.25; margin:0 0 10px; }
    .pi-badges { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px; }
    .pi-badge { font-size:11px; font-weight:700; padding:3px 10px; border-radius:100px; }
    .pi-badge-sku { background:#f3f4f6; color:#6b7280; }
    .pi-badge-cat { background:#eef2ff; color:#4f46e5; }

    .pi-price-box { border:1.5px solid #e5e7eb; border-radius:14px; padding:16px 20px; margin-bottom:20px; background:#fafafa; }
    .pi-price-label { font-size:11px; color:#9ca3af; font-weight:600; text-transform:uppercase; letter-spacing:.06em; margin-bottom:4px; }
    .pi-price { font-size:2.2rem; font-weight:900; color:#111827; line-height:1; }
    .pi-price-sub { font-size:13px; color:#9ca3af; margin-top:4px; }

    .pi-section-label { font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:#374151; margin-bottom:10px; }
    .pi-swatches { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px; }
    .pi-swatch { width:32px; height:32px; border-radius:50%; border:2.5px solid #e5e7eb; cursor:pointer; transition:all .2s; padding:0; }
    .pi-swatch:hover { transform:scale(1.15); }
    .pi-swatch.on { border-color:#4f46e5; box-shadow:0 0 0 3px rgba(79,70,229,.25); }
    .pi-cart-btn { width:100%; background:#4f46e5; color:#fff; border:none; border-radius:12px; font-size:14px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition:all .2s; margin-bottom:12px; padding:14px; }
    .pi-cart-btn:hover:not(:disabled) { background:#4338ca; transform:translateY(-1px); box-shadow:0 6px 20px rgba(79,70,229,.35); }
    .pi-cart-btn:disabled { background:#9ca3af; cursor:not-allowed; transform:none; box-shadow:none; }
    .pi-quote-btn { width:100%; border:1.5px solid #e5e7eb; background:#fff; color:#374151; border-radius:12px; padding:13px; font-size:14px; font-weight:600; cursor:pointer; transition:all .2s; margin-bottom:16px; display:flex; align-items:center; justify-content:center; gap:8px; text-decoration:none; }
    .pi-quote-btn:hover { border-color:#4f46e5; color:#4f46e5; }
    .pi-share-btn { width:100%; border:1.5px solid #e5e7eb; background:#fff; color:#6b7280; border-radius:12px; padding:11px; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition:all .2s; }
    .pi-share-btn:hover { border-color:#4f46e5; color:#4f46e5; }

    .pi-delivery { border:1px solid #e5e7eb; border-radius:14px; padding:16px; margin-bottom:16px; }
    .pi-delivery-item { display:flex; align-items:flex-start; gap:12px; padding:10px 0; border-bottom:1px solid #f1f5f9; }
    .pi-delivery-item:last-child { border-bottom:none; padding-bottom:0; }
    .pi-delivery-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .pi-delivery-text-title { font-size:13px; font-weight:700; color:#111827; }
    .pi-delivery-text-sub { font-size:12px; color:#9ca3af; margin-top:1px; }

    .pd-tabs { max-width:1280px; margin:0 auto; padding:0 24px 48px; }
    .pd-tab-bar { display:flex; border-bottom:2px solid #e5e7eb; margin-bottom:28px; overflow-x:auto; }
    .pd-tab { padding:12px 24px; font-size:14px; font-weight:600; color:#6b7280; border:none; background:none; cursor:pointer; border-bottom:3px solid transparent; margin-bottom:-2px; white-space:nowrap; transition:all .2s; }
    .pd-tab.on { color:#4f46e5; border-bottom-color:#4f46e5; }
    .pd-tab:hover:not(.on) { color:#374151; }
    .pd-desc { font-size:14px; line-height:1.85; color:#374151; }
    .pd-desc p { margin:0 0 12px; }

    .spec-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    @media(max-width:640px) { .spec-grid { grid-template-columns:1fr; } }
    .spec-box { background:#f8fafc; border-radius:14px; padding:20px 24px; }
    .spec-box-title { font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:#9ca3af; margin-bottom:14px; }
    .spec-row { display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid #e5e7eb; font-size:13px; gap:12px; }
    .spec-row:last-child { border-bottom:none; }
    .spec-key { color:#6b7280; font-weight:500; }
    .spec-val { color:#111827; font-weight:700; text-align:right; }
    .spec-package { font-size:13px; color:#6b7280; line-height:1.7; }
    .spec-features { list-style:none; padding:0; margin:0; }
    .spec-features li { font-size:13px; color:#374151; padding:6px 0; border-bottom:1px solid #e5e7eb; }
    .spec-features li:last-child { border-bottom:none; }

    .pd-related { background:#f8fafc; padding:48px 0; }
    .pd-related-inner { max-width:1280px; margin:0 auto; padding:0 24px; }
    .pd-related-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; }
    .pd-related-title { font-size:1.3rem; font-weight:800; color:#111827; }
    .pd-related-link { font-size:13px; font-weight:700; color:#4f46e5; text-decoration:none; }
    .pd-related-link:hover { color:#3730a3; }
    .pd-related-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:16px; }
    @media(min-width:640px) { .pd-related-grid { grid-template-columns:repeat(4,1fr); } }

    [x-cloak] { display:none !important; }
</style>
@endpush

@section('content')
@php
    $placeholder = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iNDAwIj48cmVjdCB3aWR0aD0iNDAwIiBoZWlnaHQ9IjQwMCIgZmlsbD0iI2Y4ZmFmYyIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg==';
    $mainImg = $product->mainImageUrl() ?? $placeholder;
    $colors = $product->variants->filter(fn ($v) => filled($v->color))->unique('color')->values();
    $allImages = $product->images->filter(fn ($i) => $i->publicUrl());

    $defaultImage = $product->mainImageUrl() ?? asset('images/placeholder.jpg');
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

    $uniqueColors = $colors;
    $primaryCategory = $product->categories->first();
    $breadcrumbCategory = $primaryCategory?->parent ?? $primaryCategory;

    $featureLines = collect(preg_split('/\r\n|\r|\n/', $product->features ?? ''))
        ->map(fn ($line) => trim($line))
        ->filter();

    $hasSizes = $product->variants->whereNotNull('size')->where('size', '!=', '')->isNotEmpty();
    $unitPrice = $product->min_msrp ? (float) $product->min_msrp : 0;
@endphp

<!-- BREADCRUMB -->
<div style="background:#f8fafc;border-bottom:1px solid #e5e7eb;">
    <div class="pdp">
        <div class="bc">
            <a href="{{ route('home') }}">Home</a>
            @if($product->brand)
                <span class="bc-sep">›</span>
                <a href="{{ route('brands.show', $product->brand) }}">{{ $product->brand->name }}</a>
            @endif
            @if($primaryCategory)
                <span class="bc-sep">›</span>
                <a href="{{ route('categories.show', $primaryCategory) }}">{{ $primaryCategory->name }}</a>
            @endif
            <span class="bc-sep">›</span>
            <span style="color:#111827;font-weight:600;">{{ Str::limit($product->name, 50) }}</span>
        </div>
    </div>
</div>

<!-- MAIN 2-COL -->
<div class="pdp">
    <div class="pdp-grid" x-data="productDetail()">
        <!-- LEFT: GALLERY -->
        <div style="position:sticky;top:80px;">
            <div class="gal-main">
                <img
                    :src="activeImg"
                    alt="{{ $product->name }}"
                    onerror="this.src='{{ $placeholder }}'"
                >
            </div>
            @if($allImages->count() > 0)
                <div class="gal-thumbs">
                    @foreach($allImages->take(8) as $img)
                        @php $u = $img->publicUrl(); @endphp
                        @if($u)
                            <div
                                class="gal-thumb"
                                :class="activeImg === '{{ addslashes($u) }}' ? 'on' : ''"
                                @click="setGallery('{{ addslashes($u) }}')"
                            >
                                <img
                                    src="{{ $u }}"
                                    alt="{{ $product->name }}"
                                    loading="lazy"
                                    onerror="this.parentElement.style.display='none'"
                                >
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        <!-- RIGHT: INFO -->
        <div>
            @if($product->brand)
                <a href="{{ route('brands.show', $product->brand) }}" class="pi-brand">{{ $product->brand->name }}</a>
            @endif
            <h1 class="pi-title">{{ $product->name }}</h1>
            <div class="pi-badges">
                <span class="pi-badge pi-badge-sku">SKU: {{ $product->parent_sku }}</span>
                @if($primaryCategory)
                    <a href="{{ route('categories.show', $primaryCategory) }}" class="pi-badge pi-badge-cat" style="text-decoration:none;">{{ $primaryCategory->name }}</a>
                @endif
            </div>

            <!-- PRICE -->
            <div class="pi-price-box">
                <div class="pi-price-label">Starting from</div>
                <div class="pi-price">
                    @if($product->min_msrp)
                        ${{ number_format($product->min_msrp, 2) }}
                    @else
                        Price on request
                    @endif
                </div>
                @if($product->max_msrp && $product->max_msrp > $product->min_msrp)
                    <div class="pi-price-sub">
                        Up to ${{ number_format($product->max_msrp, 2) }}
                    </div>
                @endif
            </div>

            <!-- COLOR -->
            @if($colors->isNotEmpty())
                <div class="pi-section-label">
                    Color
                    <span x-show="selectedColor" x-text="' — ' + selectedColor" style="font-weight:400;text-transform:none;color:#6b7280;font-size:12px;"></span>
                </div>
                <div class="pi-swatches">
                    @foreach($colors as $v)
                        <button
                            type="button"
                            class="pi-swatch"
                            :class="selectedColor === '{{ addslashes($v->color) }}' ? 'on' : ''"
                            @click="selectColor('{{ addslashes($v->color) }}')"
                            title="{{ $v->color }}"
                            style="background:{{ $v->color_hex_value ?: '#d1d5db' }};"
                            aria-label="Color {{ $v->color }}"
                        ></button>
                    @endforeach
                </div>
            @endif

            <!-- SIZE & QUANTITY -->
            @if($hasSizes)
                <div class="pi-section-label">Size & Quantity</div>
                <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:20px;">
                    <template x-for="s in availableSizes" :key="s">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:6px;">
                            <div
                                style="min-width:52px;padding:8px 14px;border-radius:10px;border:1.5px solid #e5e7eb;background:#fff;font-size:13px;font-weight:700;color:#374151;text-align:center;transition:all .2s;"
                                :style="(selectedSizes[s] || 0) > 0 ? 'border-color:#4f46e5;background:#eef2ff;color:#4f46e5;' : ''"
                                x-text="s"
                            ></div>
                            <input
                                type="number"
                                :value="selectedSizes[s] || ''"
                                @input="selectedSizes[s] = parseInt($event.target.value) || 0"
                                @focus="$event.target.select()"
                                min="0"
                                max="999"
                                placeholder="0"
                                :aria-label="'Quantity for size ' + s"
                                style="width:52px;text-align:center;border:1.5px solid #e5e7eb;border-radius:8px;padding:6px 4px;font-size:13px;font-weight:700;color:#111827;outline:none;background:#f9fafb;"
                                onfocus="this.style.borderColor='#4f46e5';this.style.background='#fff'"
                                onblur="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb'"
                            >
                        </div>
                    </template>
                </div>

                <div
                    x-show="totalQty > 0"
                    x-cloak
                    style="background:#eef2ff;border:1px solid #c7d2fe;border-radius:10px;padding:10px 16px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;"
                >
                    <span style="font-size:13px;font-weight:600;color:#4f46e5;">
                        Total: <strong x-text="totalQty + ' pcs'"></strong>
                    </span>
                    <span
                        style="font-size:13px;font-weight:700;color:#111827;"
                        x-text="'Est. $' + (totalQty * {{ floatval($product->min_msrp ?? 0) }}).toFixed(2)"
                    ></span>
                </div>
            @endif

            <form id="cart-form" action="{{ route('store.cart.add.bulk') }}" method="POST">
                @csrf
                <div id="cart-inputs-container"></div>
                <button
                    type="button"
                    class="pi-cart-btn"
                    :disabled="totalQty === 0"
                    :style="totalQty === 0 ? 'background:#9ca3af;cursor:not-allowed;' : ''"
                    @click="submitCart()"
                >
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-show="totalQty === 0">Select Color & Enter Quantities</span>
                    <span x-show="totalQty > 0" x-cloak>Add to Cart — <span x-text="totalQty"></span> pcs</span>
                </button>
            </form>

            

            <!-- DELIVERY INFO -->
            <div class="pi-delivery">
                <div class="pi-delivery-item">
                    <div class="pi-delivery-icon" style="background:#d1fae5;">
                        <svg width="18" height="18" fill="none" stroke="#059669" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                    </div>
                    <div>
                        <div class="pi-delivery-text-title">Free Shipping</div>
                        <div class="pi-delivery-text-sub">On all orders over $150</div>
                    </div>
                </div>
                <div class="pi-delivery-item">
                    <div class="pi-delivery-icon" style="background:#dbeafe;">
                        <svg width="18" height="18" fill="none" stroke="#2563eb" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <div>
                        <div class="pi-delivery-text-title">Easy Returns</div>
                        <div class="pi-delivery-text-sub">Hassle-free return policy</div>
                    </div>
                </div>
                <div class="pi-delivery-item">
                    <div class="pi-delivery-icon" style="background:#ede9fe;">
                        <svg width="18" height="18" fill="none" stroke="#7c3aed" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <div class="pi-delivery-text-title">Bulk Pricing</div>
                        <div class="pi-delivery-text-sub">Volume discounts for teams & retailers</div>
                    </div>
                </div>
            </div>

            <!-- SHARE -->
            <button
                type="button"
                class="pi-share-btn"
                onclick="navigator.share ? navigator.share({title:@js($product->name),url:window.location.href}) : (navigator.clipboard.writeText(window.location.href), alert('Link copied!'))"
            >
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                Share This Product
            </button>
        </div>
    </div>
</div>

<!-- TABS -->
<div class="pd-tabs" x-data="{ tab: 'desc' }">
    <div class="pd-tab-bar">
        <button type="button" class="pd-tab" :class="tab === 'desc' ? 'on' : ''" @click="tab = 'desc'">Description</button>
        <button type="button" class="pd-tab" :class="tab === 'specs' ? 'on' : ''" @click="tab = 'specs'">Specifications</button>
    </div>

    <!-- Description -->
    <div x-show="tab === 'desc'" class="pd-desc">
        @if(filled($product->description))
            {!! nl2br(e($product->description)) !!}
        @endif
        @if($featureLines->isNotEmpty())
            <ul class="spec-features" style="margin-top:20px;">
                @foreach($featureLines as $feature)
                    <li>{{ $feature }}</li>
                @endforeach
            </ul>
        @endif
    </div>

    <!-- Specs -->
    <div x-show="tab === 'specs'" x-cloak>
        <div class="spec-grid">
            <div class="spec-box">
                <div class="spec-box-title">Key Features</div>
                @foreach(['parent_sku' => 'Parent SKU', 'launch_date' => 'Launch Date', 'min_msrp' => 'Min Price', 'max_msrp' => 'Max Price'] as $key => $label)
                    @if($product->$key)
                        <div class="spec-row">
                            <span class="spec-key">{{ $label }}</span>
                            <span class="spec-val">
                                @if(in_array($key, ['min_msrp', 'max_msrp']))
                                    ${{ number_format((float) $product->$key, 2) }}
                                @else
                                    {{ $product->$key }}
                                @endif
                            </span>
                        </div>
                    @endif
                @endforeach
                @if($product->brand)
                    <div class="spec-row">
                        <span class="spec-key">Brand</span>
                        <span class="spec-val">{{ $product->brand->name }}</span>
                    </div>
                @endif
                @if($primaryCategory)
                    <div class="spec-row">
                        <span class="spec-key">Category</span>
                        <span class="spec-val">{{ $primaryCategory->name }}</span>
                    </div>
                @endif
            </div>
            <div class="spec-box">
                <div class="spec-box-title">What's in the Package</div>
                <div class="spec-package">
                    {{ $product->name }} — includes all selected variants as per order.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- RELATED -->
@if($related->count())
    <div class="pd-related">
        <div class="pd-related-inner">
            <div class="pd-related-head">
                <div class="pd-related-title">You May Also Like</div>
                <a href="{{ route('products.index') }}" class="pd-related-link">View all →</a>
            </div>
            <div class="pd-related-grid">
                @foreach($related->take(4) as $rp)
                    <x-product-card :product="$rp" />
                @endforeach
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
function productDetail() {
    return {
        activeImg: @json($mainImg),
        variants: @json($variantsForJs),
        hasSizes: @json($hasSizes),
        selectedColor: null,
        selectedSizes: {},
        get availableSizes() {
            if (!this.selectedColor) {
                return [...new Set(this.variants.map(v => v.size).filter(Boolean))];
            }
            return [...new Set(
                this.variants
                    .filter(v => v.color === this.selectedColor)
                    .map(v => v.size)
                    .filter(Boolean)
            )];
        },
        get totalQty() {
            if (!this.hasSizes) {
                if (this.selectedVariant) {
                    return 1;
                }
                return this.variants.length === 1 ? 1 : 0;
            }
            return Object.values(this.selectedSizes)
                .reduce((sum, q) => sum + (parseInt(q) || 0), 0);
        },
        get selectedVariant() {
            if (!this.selectedColor) return null;
            return this.variants.find(v => v.color === this.selectedColor) || null;
        },
        selectColor(color) {
            this.selectedColor = this.selectedColor === color ? null : color;
            this.selectedSizes = {};
            if (this.selectedColor) {
                const match = this.variants.find(v => v.color === color && v.main_image_url);
                if (match && match.main_image_url) {
                    this.activeImg = match.main_image_url;
                }
            }
        },
        setGallery(url) {
            this.activeImg = url;
        },
        appendCartInput(container, variantId, quantity) {
            const input1 = document.createElement('input');
            input1.type = 'hidden';
            input1.name = `items[${variantId}][variant_id]`;
            input1.value = variantId;
            container.appendChild(input1);

            const input2 = document.createElement('input');
            input2.type = 'hidden';
            input2.name = `items[${variantId}][quantity]`;
            input2.value = quantity;
            container.appendChild(input2);
        },
        submitCart() {
            if (this.totalQty === 0) {
                return;
            }

            const container = document.getElementById('cart-inputs-container');
            container.innerHTML = '';
            let added = 0;

            if (!this.hasSizes) {
                const v = this.selectedVariant || (this.variants.length === 1 ? this.variants[0] : null);
                if (!v) {
                    alert('Please select a color first.');
                    return;
                }
                this.appendCartInput(container, v.id, 1);
                added = 1;
            } else {
                for (const [size, qty] of Object.entries(this.selectedSizes)) {
                    const q = parseInt(qty) || 0;
                    if (q <= 0) continue;

                    const variant = this.variants.find(v =>
                        v.color === this.selectedColor && v.size === size
                    );
                    const fallback = !this.selectedColor
                        ? this.variants.find(v => v.size === size)
                        : null;
                    const v = variant || fallback;
                    if (!v) continue;

                    this.appendCartInput(container, v.id, q);
                    added++;
                }
            }

            if (added === 0) {
                alert('Please select a color and enter quantities first.');
                return;
            }

            document.getElementById('cart-form').submit();
        }
    };
}
</script>
@endpush
