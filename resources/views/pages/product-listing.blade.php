@extends('layouts.app')

@section('title', 'All Products')
@section('meta_description', 'Browse our full catalog of sports apparel. Filter by brand, size, color and more.')

@push('styles')
<style>
    .listing-page { max-width: 1280px; margin: 0 auto; padding: 32px 24px; }

    .listing-layout {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 32px;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .listing-layout { grid-template-columns: 1fr; }
    }

    .filter-sidebar {
        position: sticky;
        top: 80px;
        background: #fff;
        border: 1.5px solid #e5e7eb;
        border-radius: 20px;
        padding: 24px;
        max-height: calc(100vh - 100px);
        overflow-y: auto;
    }
    .filter-sidebar::-webkit-scrollbar { width: 4px; }
    .filter-sidebar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }

    .products-area { min-width: 0; }

    .filter-group { margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid #f1f5f9; }
    .filter-group:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    .filter-group-title {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: #9ca3af;
        margin-bottom: 12px;
    }

    .filter-item { display: flex; align-items: center; justify-content: space-between; padding: 5px 0; cursor: pointer; }
    .filter-item-label { font-size: 13px; color: #374151; display: flex; align-items: center; gap: 8px; }
    .filter-item-count { font-size: 11px; color: #9ca3af; background: #f3f4f6; border-radius: 100px; padding: 1px 8px; }

    .filter-colors { display: flex; flex-wrap: wrap; gap: 8px; }
    .filter-swatch {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 2px solid #e5e7eb;
        cursor: pointer;
        transition: transform .15s, border-color .15s;
        display: block;
    }
    .filter-swatch:hover,
    .filter-swatch.active { transform: scale(1.2); border-color: #4f46e5; }

    .filter-sizes { display: flex; flex-wrap: wrap; gap: 6px; }
    .filter-size-pill {
        padding: 5px 12px;
        border-radius: 8px;
        border: 1.5px solid #e5e7eb;
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        background: #fff;
        transition: all .15s;
        display: inline-block;
    }
    .filter-size-pill:hover,
    .filter-size-pill.active { border-color: #4f46e5; color: #4f46e5; background: #eef2ff; }

    .listing-active-filters {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-bottom: 24px;
        padding: 16px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
    }
    .listing-active-filters span { font-size: 13px; font-weight: 600; color: #374151; }
    .listing-filter-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        border-radius: 999px;
        background: #eef2ff;
        color: #4f46e5;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
    }
    .listing-filter-tag:hover { background: #e0e7ff; }
    .listing-clear-all { font-size: 12px; font-weight: 600; color: #6b7280; text-decoration: none; margin-left: auto; }
    .listing-clear-all:hover { color: #4f46e5; }

    .listing-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 24px;
    }
    .listing-count { font-size: 14px; color: #6b7280; margin: 0; }
    .listing-sort {
        padding: 8px 32px 8px 12px;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        font-size: 14px;
        color: #374151;
        background: #fff;
    }
    .listing-sort:focus { outline: none; border-color: #4f46e5; }

    .listing-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    @media (min-width: 640px) {
        .listing-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (min-width: 1024px) {
        .listing-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (min-width: 1280px) {
        .listing-grid { grid-template-columns: repeat(4, 1fr); }
    }

    .listing-empty {
        text-align: center;
        padding: 64px 24px;
        border: 1px dashed #e5e7eb;
        border-radius: 20px;
        background: #fff;
    }
    .listing-empty h2 { font-size: 1.25rem; font-weight: 800; color: #111827; margin: 0 0 8px; }
    .listing-empty p { color: #6b7280; font-size: 14px; margin: 0 0 24px; }
    .listing-empty a {
        display: inline-block;
        background: #4f46e5;
        color: #fff;
        padding: 12px 24px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
    }
    .listing-empty a:hover { background: #4338ca; }

    .listing-mobile-filter-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border: 1.5px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
    }
    @media (min-width: 1024px) {
        .listing-mobile-filter-btn { display: none; }
        .filter-sidebar-mobile { display: none !important; }
    }
    @media (max-width: 1023px) {
        .filter-sidebar-desktop { display: none; }
    }

    .filter-drawer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .5);
        z-index: 60;
    }
    .filter-drawer {
        position: fixed;
        left: 0;
        top: 0;
        height: 100%;
        width: 100%;
        max-width: 360px;
        background: #fff;
        z-index: 61;
        display: flex;
        flex-direction: column;
        box-shadow: 4px 0 24px rgba(0, 0, 0, .12);
    }
    .filter-drawer-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
    }
    .filter-drawer-body { flex: 1; overflow-y: auto; padding: 20px; }
    .filter-drawer-foot { padding: 16px 20px; border-top: 1px solid #e5e7eb; }
    .filter-drawer-apply {
        width: 100%;
        padding: 14px;
        background: #4f46e5;
        color: #fff;
        border: none;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
    }

    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
@php
    $removeFilterUrl = function (string $key, $valueToRemove = null) use ($catalogQuery): string {
        $query = $catalogQuery;
        if (in_array($key, ['brands', 'categories', 'colors', 'sizes'], true)) {
            $current = array_values(array_filter((array) ($query[$key] ?? [])));
            $query[$key] = array_values(array_filter($current, fn ($v) => $v !== $valueToRemove));
            if ($query[$key] === []) {
                unset($query[$key]);
            }
        } else {
            unset($query[$key]);
        }

        return route('products.index').(empty($query) ? '' : '?'.http_build_query($query));
    };
@endphp

<div
    class="listing-page"
    x-data="{
        mobileFiltersOpen: false,
        submitFilters() {
            this.syncFilterPanels();
            this.$refs.filterForm.requestSubmit();
        },
        syncFilterPanels() {
            const lg = window.innerWidth >= 1024;
            this.$refs.desktopFilters?.querySelectorAll('input, select').forEach(el => { el.disabled = !lg; });
            this.$refs.mobileFilters?.querySelectorAll('input, select').forEach(el => { el.disabled = lg; });
        },
        init() {
            this.syncFilterPanels();
            window.addEventListener('resize', () => this.syncFilterPanels());
        }
    }"
    x-effect="document.body.style.overflow = mobileFiltersOpen ? 'hidden' : ''"
>
    <x-breadcrumb :items="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Products'],
    ]" />

    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;margin-bottom:24px;">
        <h1 style="font-size:1.75rem;font-weight:800;color:#111827;margin:0;">All Products</h1>
        <button type="button" class="listing-mobile-filter-btn" @click="mobileFiltersOpen = true; $nextTick(() => syncFilterPanels())">
            Filter
            @if($activeFilterTags->count())
                <span style="background:#4f46e5;color:#fff;border-radius:999px;padding:2px 8px;font-size:11px;">{{ $activeFilterTags->count() }}</span>
            @endif
        </button>
    </div>

    <form
        method="GET"
        action="{{ route('products.index') }}"
        x-ref="filterForm"
        @change="submitFilters()"
        @submit="syncFilterPanels()"
    >
        @if($activeFilterTags->isNotEmpty())
            <div class="listing-active-filters">
                <span>Active filters:</span>
                @foreach($activeFilterTags as $filter)
                    <a href="{{ $removeFilterUrl($filter['key'], $filter['value']) }}" class="listing-filter-tag">
                        {{ $filter['label'] }} &times;
                    </a>
                @endforeach
                <a href="{{ route('products.index') }}" class="listing-clear-all">Clear all</a>
            </div>
        @endif

        <div class="listing-layout">
            <aside class="filter-sidebar filter-sidebar-desktop">
                <div x-ref="desktopFilters">
                    <x-product-listing-filters
                        id-prefix="desktop-"
                        :clear-filters-url="route('products.index')"
                        :all-brands="$allBrands"
                        :all-categories="$allCategories"
                        :all-colors="$allColors"
                        :all-sizes="$allSizes"
                        :price-floor="$priceFloor"
                        :price-ceiling="$priceCeiling"
                    />
                </div>
            </aside>

            <div class="products-area">
                <div class="listing-toolbar">
                    <p class="listing-count">
                        @if($products->total() > 0)
                            Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ number_format($products->total()) }} products
                        @else
                            Showing 0 products
                        @endif
                    </p>
                    <select id="catalog-sort" name="sort" class="listing-sort">
                        <option value="" @selected(!request('sort'))>Newest</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: Low to High</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: High to Low</option>
                        <option value="newest" @selected(request('sort') === 'newest')>Newest Arrivals</option>
                    </select>
                </div>

                @if($products->count())
                    <div class="listing-grid">
                        @foreach($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>
                    <div style="margin-top:40px;">
                        {{ $products->links('components.pagination') }}
                    </div>
                @else
                    <div class="listing-empty">
                        <h2>No products found</h2>
                        <p>Try adjusting your filters or browse the full catalog.</p>
                        <a href="{{ route('products.index') }}">Clear filters</a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Mobile filter drawer --}}
        <div x-show="mobileFiltersOpen" x-cloak class="filter-sidebar-mobile">
            <div class="filter-drawer-overlay" @click="mobileFiltersOpen = false"></div>
            <aside class="filter-drawer" @click.stop>
                <div class="filter-drawer-head">
                    <strong style="font-size:16px;color:#111827;">Filters</strong>
                    <button type="button" style="background:none;border:none;font-size:24px;cursor:pointer;color:#6b7280;" @click="mobileFiltersOpen = false" aria-label="Close">&times;</button>
                </div>
                <div class="filter-drawer-body" x-ref="mobileFilters">
                    <x-product-listing-filters
                        id-prefix="mobile-"
                        :clear-filters-url="route('products.index')"
                        :all-brands="$allBrands"
                        :all-categories="$allCategories"
                        :all-colors="$allColors"
                        :all-sizes="$allSizes"
                        :price-floor="$priceFloor"
                        :price-ceiling="$priceCeiling"
                    />
                </div>
                <div class="filter-drawer-foot">
                    <button type="button" class="filter-drawer-apply" @click="mobileFiltersOpen = false; submitFilters()">Apply Filters</button>
                </div>
            </aside>
        </div>
    </form>
</div>
@endsection
