@extends('layouts.app')

@section('title', $category->name)
@section('meta_description', 'Shop ' . $category->name . ' sports apparel at Momentec.')

@section('schema_json')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BreadcrumbList",
    "itemListElement": [
        {
            "@@type": "ListItem",
            "position": 1,
            "name": "Home",
            "item": "{{ route('home') }}"
        },
        @if($category->parent)
        {
            "@@type": "ListItem",
            "position": 2,
            "name": @json($category->parent->name),
            "item": "{{ route('categories.show', $category->parent) }}"
        },
        @endif
        {
            "@@type": "ListItem",
            "position": {{ $category->parent ? 3 : 2 }},
            "name": @json($category->name),
            "item": "{{ route('categories.show', $category) }}"
        }
    ]
}
</script>
@endsection

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
    $filterParamMap = [
        'Brand' => 'brand',
        'Color' => 'color',
        'Size' => 'size',
        'Min Price' => 'min_price',
        'Max Price' => 'max_price',
    ];

    $activeFilters = [];
    if (request()->query('brand')) {
        $brandMatch = $allBrands->firstWhere('slug', request()->query('brand'));
        $activeFilters['Brand'] = $brandMatch?->name ?? request()->query('brand');
    }
    if (request()->query('color')) {
        $activeFilters['Color'] = request()->query('color');
    }
    if (request()->query('size')) {
        $activeFilters['Size'] = request()->query('size');
    }
    if (request()->query('min_price') && (int) request()->query('min_price') > $priceFloor) {
        $activeFilters['Min Price'] = '$'.number_format((int) request()->query('min_price'), 0);
    }
    if (request()->query('max_price') && (int) request()->query('max_price') < $priceCeiling) {
        $activeFilters['Max Price'] = '$'.number_format((int) request()->query('max_price'), 0);
    }

    $categoryFormAction = route('categories.show', $category);
    $categoryClearUrl = route('categories.show', $category);
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
    <div style="background:linear-gradient(135deg,#1a1a2e,#2d2b55);border-radius:20px;padding:32px 36px;margin-bottom:28px;position:relative;overflow:hidden;">
        <div style="position:absolute;top:-20px;right:-20px;width:120px;height:120px;border-radius:50%;background:rgba(79,70,229,.15);"></div>
        <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:rgba(255,255,255,.5);margin-bottom:12px;">
            <a href="{{ route('home') }}" style="color:rgba(255,255,255,.5);text-decoration:none;">Home</a>
            @if($category->parent)
                <span>›</span>
                <a href="{{ route('categories.show', $category->parent) }}" style="color:rgba(255,255,255,.5);text-decoration:none;">{{ $category->parent->name }}</a>
            @endif
            <span>›</span>
            <span style="color:#fff;">{{ $category->name }}</span>
        </div>
        <h1 style="font-size:clamp(1.6rem,3vw,2.2rem);font-weight:900;color:#fff;margin:0 0 8px;">{{ $category->name }}</h1>
        <p style="font-size:14px;color:rgba(255,255,255,.5);margin:0;">{{ $products->total() }} products in this category</p>
    </div>

    @if($category->children->count())
        <div style="margin-bottom:28px;">
            <div style="font-size:13px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px;">Subcategories</div>
            <div style="display:flex;flex-wrap:wrap;gap:10px;">
                @foreach($category->children as $child)
                    <a
                        href="{{ route('categories.show', $child) }}"
                        style="display:inline-flex;align-items:center;gap:6px;background:#fff;border:1.5px solid #e5e7eb;border-radius:12px;padding:8px 16px;font-size:13px;font-weight:600;color:#374151;text-decoration:none;transition:all .2s;"
                        onmouseover="this.style.borderColor='#4f46e5';this.style.color='#4f46e5'"
                        onmouseout="this.style.borderColor='#e5e7eb';this.style.color='#374151'"
                    >
                        {{ $child->name }}
                        @if($child->products_count ?? 0)
                            <span style="background:#f3f4f6;border-radius:100px;padding:1px 7px;font-size:11px;color:#9ca3af;">{{ $child->products_count }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:flex-end;gap:16px;margin-bottom:24px;">
        <button type="button" class="listing-mobile-filter-btn" @click="mobileFiltersOpen = true; $nextTick(() => syncFilterPanels())">
            Filter
            @if(count($activeFilters))
                <span style="background:#4f46e5;color:#fff;border-radius:999px;padding:2px 8px;font-size:11px;">{{ count($activeFilters) }}</span>
            @endif
        </button>
    </div>

    <form
        method="GET"
        action="{{ $categoryFormAction }}"
        x-ref="filterForm"
        @change="submitFilters()"
        @submit="syncFilterPanels()"
    >
        <div class="listing-layout">
            <aside class="filter-sidebar filter-sidebar-desktop">
                <div x-ref="desktopFilters">
                    <x-product-listing-filters
                        id-prefix="desktop-"
                        :hide-categories="true"
                        :clear-filters-url="$categoryClearUrl"
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
                @if(count($activeFilters) > 0)
                    <div style="display:flex;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:20px;">
                        <span style="font-size:12px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;">Active:</span>
                        @foreach($activeFilters as $key => $val)
                            <a
                                href="{{ request()->fullUrlWithoutQuery([$filterParamMap[$key]]) }}"
                                style="display:inline-flex;align-items:center;gap:6px;background:#eef2ff;border:1px solid #c7d2fe;border-radius:100px;padding:4px 12px;font-size:12px;font-weight:600;color:#4f46e5;text-decoration:none;"
                            >
                                {{ $key }}: {{ $val }}
                                <span style="font-size:14px;line-height:1;">×</span>
                            </a>
                        @endforeach
                        <a
                            href="{{ strtok(request()->fullUrl(), '?') }}"
                            style="font-size:12px;font-weight:600;color:#ef4444;text-decoration:none;"
                        >Clear all</a>
                    </div>
                @endif

                <div class="listing-toolbar">
                    <p class="listing-count">
                        @if($products->total() > 0)
                            Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ number_format($products->total()) }} products
                        @else
                            Showing 0 products
                        @endif
                    </p>
                    <select id="catalog-sort" name="sort" class="listing-sort">
                        <option value="" @selected(!request()->query('sort'))>Newest</option>
                        <option value="price_asc" @selected(request()->query('sort') === 'price_asc')>Price: Low to High</option>
                        <option value="price_desc" @selected(request()->query('sort') === 'price_desc')>Price: High to Low</option>
                        <option value="newest" @selected(request()->query('sort') === 'newest')>Newest Arrivals</option>
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
                        <p>Try adjusting your filters or browse other categories.</p>
                        <a href="{{ $categoryClearUrl }}">Clear filters</a>
                    </div>
                @endif
            </div>
        </div>

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
                        :hide-categories="true"
                        :clear-filters-url="$categoryClearUrl"
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
