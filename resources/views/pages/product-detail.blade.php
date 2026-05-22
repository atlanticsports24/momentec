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
    .pd-wrap { max-width:1280px; margin:0 auto; padding:32px 24px; }
    .pd-grid { display:grid; grid-template-columns:1fr 1fr; gap:48px; align-items:start; }
    @media(max-width:768px) { .pd-grid { grid-template-columns:1fr; } }

    .pd-gallery { position:sticky; top:80px; }
    .pd-main-wrap { aspect-ratio:1/1; border-radius:20px; overflow:hidden; background:#f8fafc; border:1.5px solid #e5e7eb; margin-bottom:12px; cursor:zoom-in; }
    .pd-main-img { width:100%; height:100%; object-fit:contain; transition:transform .4s; }
    .pd-main-wrap:hover .pd-main-img { transform:scale(1.06); }
    .pd-thumbs { display:flex; gap:8px; flex-wrap:wrap; }
    .pd-thumb { width:70px; height:70px; border-radius:12px; overflow:hidden; border:2px solid #e5e7eb; cursor:pointer; background:#f8fafc; transition:border-color .2s; flex-shrink:0; padding:0; }
    .pd-thumb.active, .pd-thumb:hover { border-color:#4f46e5; }
    .pd-thumb img { width:100%; height:100%; object-fit:contain; display:block; }

    .pd-info { padding:4px 0; }
    .pd-brand-link { font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.1em; color:#4f46e5; text-decoration:none; }
    .pd-brand-link:hover { color:#3730a3; }
    .pd-title { font-size:clamp(1.3rem,2.5vw,1.75rem); font-weight:900; color:#111827; line-height:1.25; margin:8px 0 10px; }
    .pd-meta { display:flex; align-items:center; gap:16px; margin-bottom:16px; flex-wrap:wrap; }
    .pd-sku { font-size:12px; color:#9ca3af; }
    .pd-cat-pill { font-size:12px; background:#eef2ff; color:#4f46e5; border-radius:100px; padding:3px 10px; text-decoration:none; font-weight:600; }
    .pd-price-box { background:linear-gradient(135deg,#f8fafc,#fff); border:1.5px solid #e5e7eb; border-radius:16px; padding:16px 20px; margin-bottom:20px; }
    .pd-price-from { font-size:12px; color:#9ca3af; font-weight:500; }
    .pd-price-main { font-size:2rem; font-weight:900; color:#111827; line-height:1; margin:4px 0; }
    .pd-price-range { font-size:13px; color:#6b7280; }
    .pd-divider { height:1px; background:#f1f5f9; margin:18px 0; }
    .pd-label { font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:#374151; margin-bottom:10px; }
    .pd-label-muted { font-weight:400; text-transform:none; color:#6b7280; }
    .pd-swatches { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px; }
    .pd-swatch { width:34px; height:34px; border-radius:50%; border:2.5px solid #e5e7eb; cursor:pointer; transition:all .2s; position:relative; padding:0; }
    .pd-swatch:hover { transform:scale(1.15); }
    .pd-swatch.ring { border-color:#4f46e5; box-shadow:0 0 0 3px rgba(79,70,229,.2); }
    .pd-sizes { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px; }
    .pd-size { padding:8px 16px; border-radius:10px; border:1.5px solid #e5e7eb; background:#fff; font-size:13px; font-weight:600; color:#374151; cursor:pointer; transition:all .2s; }
    .pd-size:hover:not(:disabled) { border-color:#4f46e5; color:#4f46e5; }
    .pd-size.active { background:#4f46e5; color:#fff; border-color:#4f46e5; }
    .pd-size:disabled { cursor:not-allowed; color:#9ca3af; text-decoration:line-through; opacity:.6; }
    .pd-size-chart-btn { display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:700; color:#4f46e5; background:none; border:none; cursor:pointer; padding:0; margin-bottom:18px; }
    .pd-size-chart-btn:hover { color:#3730a3; }
    .pd-variant-box { background:#f8fafc; border:1px solid #e5e7eb; border-radius:14px; padding:16px 20px; margin-bottom:20px; display:grid; grid-template-columns:1fr 1fr; gap:10px; font-size:13px; }
    .pd-v-label { color:#9ca3af; font-weight:500; font-size:11px; text-transform:uppercase; letter-spacing:.05em; }
    .pd-v-val { color:#111827; font-weight:700; margin-top:2px; }
    .pd-btn-primary { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; background:#4f46e5; color:#fff; border:none; border-radius:14px; padding:15px; font-size:15px; font-weight:700; cursor:pointer; transition:all .2s; text-decoration:none; margin-bottom:12px; }
    .pd-btn-primary:hover { background:#4338ca; transform:translateY(-1px); box-shadow:0 8px 24px rgba(79,70,229,.3); }
    .pd-btn-secondary { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; background:#fff; color:#374151; border:1.5px solid #e5e7eb; border-radius:14px; padding:13px; font-size:14px; font-weight:600; cursor:pointer; transition:all .2s; }
    .pd-btn-secondary:hover { border-color:#4f46e5; color:#4f46e5; }
    .pd-trust { display:flex; gap:12px; flex-wrap:wrap; margin-top:16px; }
    .pd-trust-item { display:flex; align-items:center; gap:6px; font-size:12px; color:#6b7280; }
    .pd-trust-item svg { width:14px; height:14px; color:#059669; flex-shrink:0; }

    .pd-tabs-wrap { max-width:1280px; margin:0 auto; padding:0 24px 48px; }
    .pd-tab-bar { display:flex; border-bottom:2px solid #e5e7eb; margin-bottom:28px; gap:0; overflow-x:auto; }
    .pd-tab { padding:12px 24px; font-size:14px; font-weight:600; color:#6b7280; border:none; background:none; cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-2px; white-space:nowrap; transition:all .2s; }
    .pd-tab.on { color:#4f46e5; border-bottom-color:#4f46e5; }
    .pd-tab:hover { color:#374151; }
    .pd-desc { font-size:14px; line-height:1.8; color:#374151; }
    .pd-desc p { margin:0 0 12px; }
    .pd-features { list-style:none; padding:0; margin:20px 0 0; display:grid; gap:10px; }
    @media(min-width:640px) { .pd-features { grid-template-columns:repeat(2,1fr); } }
    .pd-features li { display:flex; align-items:flex-start; gap:8px; font-size:14px; color:#374151; }
    .pd-features svg { width:18px; height:18px; flex-shrink:0; color:#059669; margin-top:2px; }
    .pd-table { width:100%; border-collapse:collapse; font-size:13px; }
    .pd-table th { background:#f8fafc; padding:11px 16px; text-align:left; font-weight:700; color:#6b7280; border-bottom:1px solid #e5e7eb; font-size:11px; text-transform:uppercase; letter-spacing:.06em; }
    .pd-table th button { background:none; border:none; cursor:pointer; font:inherit; color:inherit; padding:0; display:inline-flex; align-items:center; gap:4px; }
    .pd-table th button:hover { color:#4f46e5; }
    .pd-table td { padding:11px 16px; border-bottom:1px solid #f1f5f9; color:#374151; }
    .pd-table tr:last-child td { border-bottom:none; }
    .pd-table tr:hover td { background:#fafafa; }
    .pd-table tr.row-selected td { background:#eef2ff; }
    .pd-table-sort { color:#4f46e5; }
    .pd-color-cell { display:flex; align-items:center; gap:8px; }
    .pd-color-dot { width:14px; height:14px; border-radius:50%; border:1px solid #e5e7eb; flex-shrink:0; display:inline-block; }
    .pd-sku-cell { font-family:monospace; font-size:12px; color:#4f46e5; }
    .pd-price-cell { font-weight:700; }
    .pd-upc-cell { font-size:12px; color:#9ca3af; }
    .pd-status-badge { border-radius:100px; padding:3px 10px; font-size:11px; font-weight:700; display:inline-block; }
    .pd-status-badge.active { background:#d1fae5; color:#059669; }
    .pd-status-badge.inactive { background:#f3f4f6; color:#6b7280; }
    .pd-imgs-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; }
    @media(max-width:640px) { .pd-imgs-grid { grid-template-columns:repeat(2,1fr); } }
    .pd-imgs-item { aspect-ratio:1/1; border-radius:14px; overflow:hidden; border:1px solid #e5e7eb; background:#f8fafc; cursor:pointer; padding:0; width:100%; }
    .pd-imgs-item img { width:100%; height:100%; object-fit:contain; display:block; }

    .pd-related { background:#f8fafc; padding:48px 0; }
    .pd-related-inner { max-width:1280px; margin:0 auto; padding:0 24px; }
    .pd-related-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; }
    .pd-related-title { font-size:1.3rem; font-weight:800; color:#111827; margin:0; }
    .pd-related-link { font-size:13px; font-weight:700; color:#4f46e5; text-decoration:none; }
    .pd-related-link:hover { color:#3730a3; }
    .pd-related-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:16px; }
    @media(min-width:640px) { .pd-related-grid { grid-template-columns:repeat(3,1fr); } }
    @media(min-width:1024px) { .pd-related-grid { grid-template-columns:repeat(4,1fr); } }

    .pd-modal { position:fixed; inset:0; z-index:70; display:flex; align-items:center; justify-content:center; padding:16px; }
    .pd-modal-backdrop { position:absolute; inset:0; background:rgba(0,0,0,.6); }
    .pd-modal-panel { position:relative; max-height:90vh; max-width:48rem; width:100%; overflow:auto; border-radius:16px; background:#fff; padding:20px; box-shadow:0 25px 50px rgba(0,0,0,.2); }
    .pd-modal-close { position:absolute; right:12px; top:12px; width:36px; height:36px; border:none; border-radius:8px; background:#f8fafc; color:#6b7280; cursor:pointer; display:flex; align-items:center; justify-content:center; }
    .pd-modal-close:hover { background:#f1f5f9; color:#111827; }
    .pd-modal-title { font-size:1.125rem; font-weight:800; color:#111827; margin:0 0 16px; }
    .pd-modal-img { width:100%; border-radius:12px; display:block; }

    [x-cloak] { display:none !important; }
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

<div x-data="productDetailState">
    {{-- SECTION 1 — Breadcrumb --}}
    <div style="background:#f8fafc;border-bottom:1px solid #e5e7eb;padding:10px 0;">
        <div style="max-width:1280px;margin:0 auto;padding:0 24px;display:flex;align-items:center;gap:8px;font-size:12px;color:#6b7280;">
            <a href="{{ route('home') }}" style="color:#6b7280;text-decoration:none;">🏠</a>
            <span>›</span>
            @if($breadcrumbCategory)
                <a href="{{ route('categories.show', $breadcrumbCategory) }}" style="color:#6b7280;text-decoration:none;">{{ $breadcrumbCategory->name }}</a>
                <span>›</span>
            @else
                <a href="{{ route('products.index') }}" style="color:#6b7280;text-decoration:none;">Products</a>
                <span>›</span>
            @endif
            <span style="color:#111827;font-weight:600;">{{ Str::limit($product->name, 40) }}</span>
        </div>
    </div>

    {{-- SECTION 2 — Main Product Area --}}
    <div class="pd-wrap">
        <div class="pd-grid">
            {{-- LEFT — Gallery --}}
            <div class="pd-gallery">
                <div class="pd-main-wrap">
                    <img
                        class="pd-main-img"
                        :src="displayImage"
                        alt="{{ $product->name }}"
                        width="600"
                        height="600"
                        onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iNDAwIj48cmVjdCB3aWR0aD0iNDAwIiBoZWlnaHQ9IjQwMCIgZmlsbD0iI2YzZjRmNiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg=='"
                    >
                </div>
                @if($galleryImages->count() > 1)
                    <div class="pd-thumbs">
                        @foreach($galleryImages->take(6) as $image)
                            <button
                                type="button"
                                class="pd-thumb"
                                :class="{ active: activeImageUrl === @js($image['url']) }"
                                @click="selectGalleryImage(@js($image['url']))"
                                aria-label="View {{ $image['alt'] }}"
                            >
                                <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" onerror="this.parentElement.style.display='none'">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- RIGHT — Product Info --}}
            <div class="pd-info">
                @if($product->brand)
                    <a href="{{ route('brands.show', $product->brand) }}" class="pd-brand-link">{{ $product->brand->name }}</a>
                @endif

                <h1 class="pd-title">{{ $product->name }}</h1>

                <div class="pd-meta">
                    <span class="pd-sku">SKU: {{ $product->parent_sku }}</span>
                    @if($primaryCategory)
                        <a href="{{ route('categories.show', $primaryCategory) }}" class="pd-cat-pill">{{ $primaryCategory->name }}</a>
                    @endif
                </div>

                @if($product->min_msrp)
                    <div class="pd-price-box">
                        <div class="pd-price-from">Starting from</div>
                        <div class="pd-price-main">${{ number_format($product->min_msrp, 2) }}</div>
                        @if($product->max_msrp && $product->max_msrp > $product->min_msrp)
                            <div class="pd-price-range">Up to ${{ number_format($product->max_msrp, 2) }} depending on variant</div>
                        @endif
                    </div>
                @endif

                <div class="pd-divider"></div>

                @if($uniqueColors->isNotEmpty())
                    <div class="pd-label">
                        Color
                        <span x-show="selectedColor" x-text="' — ' + selectedColor" class="pd-label-muted"></span>
                    </div>
                    <div class="pd-swatches">
                        @foreach($uniqueColors as $variant)
                            <button
                                type="button"
                                class="pd-swatch"
                                :class="{ ring: selectedColor === @js($variant->color) }"
                                @click="selectColor(@js($variant->color))"
                                title="{{ $variant->color }}"
                                style="background-color:{{ $variant->color_hex_value ?: '#d1d5db' }};"
                                aria-label="Color {{ $variant->color }}"
                            ></button>
                        @endforeach
                    </div>
                @endif

                @if($hasSizes)
                    <div class="pd-label">Size</div>
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

                <div x-show="selectedVariant" x-cloak class="pd-variant-box">
                    <div>
                        <div class="pd-v-label">Item SKU</div>
                        <div class="pd-v-val" x-text="selectedVariant?.item_sku || '—'"></div>
                    </div>
                    <div>
                        <div class="pd-v-label">Price</div>
                        <div class="pd-v-val" x-text="selectedVariant?.msrp ? '$' + Number(selectedVariant.msrp).toFixed(2) : '—'"></div>
                    </div>
                    <div>
                        <div class="pd-v-label">UPC</div>
                        <div class="pd-v-val" x-text="selectedVariant?.upc_code || '—'"></div>
                    </div>
                    <div>
                        <div class="pd-v-label">Weight</div>
                        <div class="pd-v-val" x-text="selectedVariant?.weight ? selectedVariant.weight + ' ' + (selectedVariant.weight_unit || '') : '—'"></div>
                    </div>
                    <div>
                        <div class="pd-v-label">Origin</div>
                        <div class="pd-v-val" x-text="selectedVariant?.country_of_origin || '—'"></div>
                    </div>
                    <div>
                        <div class="pd-v-label">Status</div>
                        <div class="pd-v-val" x-text="selectedVariant?.status || '—'"></div>
                    </div>
                </div>

                <div class="pd-divider"></div>

                <a href="{{ route('products.index') }}" class="pd-btn-primary">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Request a Quote
                </a>
                <button type="button" class="pd-btn-secondary" @click="shareProduct()">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    Share This Product
                </button>

                <div class="pd-trust">
                    <span class="pd-trust-item">
                        <svg fill="none" stroke="#059669" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Free shipping $150+
                    </span>
                    <span class="pd-trust-item">
                        <svg fill="none" stroke="#059669" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Easy returns
                    </span>
                    <span class="pd-trust-item">
                        <svg fill="none" stroke="#059669" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Bulk pricing available
                    </span>
                </div>
            </div>
        </div>
    </div>

    @if($sizeChartUrl)
        <div x-show="sizeChartOpen" x-cloak class="pd-modal" role="dialog" aria-modal="true" aria-label="Size chart">
            <div class="pd-modal-backdrop" @click="sizeChartOpen = false"></div>
            <div class="pd-modal-panel" @click.stop>
                <button type="button" class="pd-modal-close" @click="sizeChartOpen = false" aria-label="Close">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <h2 class="pd-modal-title">Size Chart</h2>
                <img class="pd-modal-img" :src="sizeChartUrl" alt="Size chart for {{ $product->name }}">
            </div>
        </div>
    @endif

    {{-- SECTION 3 — Description + Variants + Images Tabs --}}
    @if($hasDescriptionTab || $hasVariantsTab || $hasImagesTab)
        <div class="pd-tabs-wrap">
            <div class="pd-tab-bar" role="tablist">
                @if($hasDescriptionTab)
                    <button type="button" class="pd-tab" :class="activeTab === 'description' ? 'on' : ''" @click="activeTab = 'description'" role="tab">Description</button>
                @endif
                @if($hasVariantsTab)
                    <button type="button" class="pd-tab" :class="activeTab === 'variants' ? 'on' : ''" @click="activeTab = 'variants'" role="tab">
                        All Variants ({{ $product->variants->count() }})
                    </button>
                @endif
                @if($hasImagesTab)
                    <button type="button" class="pd-tab" :class="activeTab === 'images' ? 'on' : ''" @click="activeTab = 'images'" role="tab">
                        All Images ({{ $product->images->count() }})
                    </button>
                @endif
            </div>

            @if($hasDescriptionTab)
                <div x-show="activeTab === 'description'" class="pd-desc">
                    @if(filled($product->description))
                        {!! nl2br(e($product->description)) !!}
                    @endif
                    @if($featureLines->isNotEmpty())
                        <ul class="pd-features">
                            @foreach($featureLines as $feature)
                                <li>
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            @if($hasVariantsTab)
                <div x-show="activeTab === 'variants'" x-cloak>
                    <div style="overflow-x:auto;">
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
                                        <td class="pd-sku-cell" x-text="variant.item_sku || '—'"></td>
                                        <td>
                                            <div class="pd-color-cell">
                                                <span
                                                    class="pd-color-dot"
                                                    x-show="variant.color_hex_value"
                                                    :style="'background:' + (variant.color_hex_value || '#d1d5db')"
                                                ></span>
                                                <span x-text="variant.color || '—'"></span>
                                            </div>
                                        </td>
                                        <td x-text="variant.size || '—'"></td>
                                        <td class="pd-price-cell" x-text="variant.msrp ? '$' + Number(variant.msrp).toFixed(2) : '—'"></td>
                                        <td class="pd-upc-cell" x-text="variant.upc_code || '—'"></td>
                                        <td>
                                            <span
                                                class="pd-status-badge"
                                                :class="variant.status && String(variant.status).toLowerCase() === 'active' ? 'active' : 'inactive'"
                                                x-text="variant.status ? variant.status.charAt(0).toUpperCase() + variant.status.slice(1) : 'N/A'"
                                            ></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($hasImagesTab)
                <div x-show="activeTab === 'images'" x-cloak>
                    <div class="pd-imgs-grid">
                        @foreach($galleryImages as $image)
                            <button
                                type="button"
                                class="pd-imgs-item"
                                @click="selectGalleryImage(@js($image['url'])); window.scrollTo({ top: 0, behavior: 'smooth' })"
                            >
                                <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" onerror="this.parentElement.style.display='none'">
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- SECTION 4 — Related Products --}}
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
