@props([
    'products',
    'allBrands',
    'allCategories',
    'allColors',
    'allSizes',
    'priceFloor' => 0,
    'priceCeiling' => 500,
    'formAction',
    'clearFiltersUrl',
    'pageTitle',
    'breadcrumbItems' => [],
    'hideBrandFilter' => false,
    'lockedBrandSlug' => null,
    'emptyClearUrl' => null,
    'activeFilterTags' => null,
    'catalogQuery' => [],
])

@php
    $clearFiltersUrl = $clearFiltersUrl ?? $formAction;
    $emptyClearUrl = $emptyClearUrl ?? $clearFiltersUrl;

    $activeFilterTags = is_array($activeFilterTags) ? $activeFilterTags : ($activeFilterTags?->all() ?? []);
    $activeFilterCount = count($activeFilterTags);
    $catalogQuery = $catalogQuery ?? [];

    $removeFilterUrl = function (string $key) use ($formAction, $catalogQuery): string {
        $query = $catalogQuery;
        unset($query[$key]);

        $base = strtok($formAction, '?') ?: $formAction;

        return $base.(empty($query) ? '' : '?'.http_build_query($query));
    };
@endphp

<style>
    @media (max-width: 1024px) {
        .listing-layout { grid-template-columns: 1fr !important; }
        .filter-sidebar.hidden.lg\:block { display: none !important; }
    }
</style>

<div
    x-data="{
        mobileFiltersOpen: false,
        gridCols: localStorage.getItem('product_grid_cols') || '4',
        setGridCols(cols) {
            this.gridCols = cols;
            localStorage.setItem('product_grid_cols', cols);
        },
        syncFilterPanels() {
            const lg = window.innerWidth >= 1024;
            this.$refs.desktopFilters?.querySelectorAll('input, select').forEach(el => { el.disabled = !lg; });
            this.$refs.mobileFilters?.querySelectorAll('input, select').forEach(el => { el.disabled = lg; });
        },
        submitFilters() {
            this.syncFilterPanels();
            this.$refs.filterForm.requestSubmit();
        },
        init() {
            this.syncFilterPanels();
            window.addEventListener('resize', () => this.syncFilterPanels());
        }
    }"
    x-effect="document.body.style.overflow = mobileFiltersOpen ? 'hidden' : ''"
>
    @if(count($breadcrumbItems))
        <x-breadcrumb :items="$breadcrumbItems" />
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary">{{ $pageTitle }}</h1>
            @if(isset($subtitle))
                <p class="mt-1 text-gray-600">{{ $subtitle }}</p>
            @endif
            {{ $header ?? '' }}
        </div>
        <button
            type="button"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm lg:hidden"
            @click="mobileFiltersOpen = true; $nextTick(() => syncFilterPanels())"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            Filter
            @if($activeFilterCount > 0)
                <span class="flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-accent px-1.5 text-xs font-bold text-white">
                    {{ $activeFilterCount }}
                </span>
            @endif
        </button>
    </div>

    {{ $beforeGrid ?? '' }}

    <form
        method="GET"
        action="{{ $formAction }}"
        x-ref="filterForm"
        class="mt-6"
        @change="submitFilters()"
        @submit="syncFilterPanels()"
    >
        @if($lockedBrandSlug)
            <input type="hidden" name="brand" value="{{ $lockedBrandSlug }}">
        @endif

        @if($activeFilterCount > 0)
            <div class="mb-6 flex flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-white p-4">
                <span class="text-sm font-medium text-gray-700">Active filters:</span>
                @foreach($activeFilterTags as $filter)
                    <a
                        href="{{ $removeFilterUrl($filter['key']) }}"
                        class="inline-flex items-center gap-1 rounded-full bg-accent-light px-3 py-1 text-xs font-semibold text-accent hover:bg-accent/10"
                    >
                        {{ $filter['label'] }}
                        <span aria-hidden="true">&times;</span>
                    </a>
                @endforeach
                <a href="{{ $clearFiltersUrl }}" class="text-xs font-semibold text-gray-500 hover:text-accent">Clear all</a>
            </div>
        @endif

        <div class="listing-layout" style="display:grid;grid-template-columns:280px 1fr;gap:32px;align-items:start;">
            <aside class="filter-sidebar hidden lg:block" style="position:sticky;top:80px;background:#fff;border:1.5px solid #e5e7eb;border-radius:20px;padding:24px;max-height:calc(100vh - 100px);overflow-y:auto;">
                <h2 style="font-size:16px;font-weight:800;color:#111827;margin:0 0 16px;">Filters</h2>
                    <div x-ref="desktopFilters">
                        <x-product-listing-filters
                            id-prefix="desktop-"
                            :hide-brands="$hideBrandFilter"
                            :clear-filters-url="$clearFiltersUrl"
                            :all-brands="$allBrands"
                            :all-categories="$allCategories"
                            :all-colors="$allColors"
                            :all-sizes="$allSizes"
                            :price-floor="$priceFloor"
                            :price-ceiling="$priceCeiling"
                        />
                    </div>
            </aside>

            <div class="products-area" style="min-width:0;">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 pb-4">
                    <p class="text-sm text-gray-600">
                        @if($products->total() > 0)
                            Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ number_format($products->total()) }} products
                        @else
                            Showing 0 products
                        @endif
                    </p>
                    <div class="flex flex-wrap items-center gap-3">
                        <label for="catalog-sort" class="sr-only">Sort products</label>
                        <select
                            id="catalog-sort"
                            name="sort"
                            class="rounded-lg border border-gray-300 py-2 pl-3 pr-8 text-sm focus:border-accent focus:ring-2 focus:ring-accent"
                        >
                            <option value="" @selected(!request('sort'))>Newest</option>
                            <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: Low to High</option>
                            <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: High to Low</option>
                            <option value="newest" @selected(request('sort') === 'newest')>Newest Arrivals</option>
                        </select>
                        <div class="hidden items-center gap-1 rounded-lg border border-gray-200 p-1 sm:flex" role="group" aria-label="Grid columns">
                            <button type="button" @click="setGridCols('2')" :class="gridCols === '2' ? 'bg-accent text-white' : 'text-gray-600 hover:bg-gray-100'" class="rounded px-2.5 py-1.5 text-xs font-semibold">2</button>
                            <button type="button" @click="setGridCols('3')" :class="gridCols === '3' ? 'bg-accent text-white' : 'text-gray-600 hover:bg-gray-100'" class="rounded px-2.5 py-1.5 text-xs font-semibold">3</button>
                            <button type="button" @click="setGridCols('4')" :class="gridCols === '4' ? 'bg-accent text-white' : 'text-gray-600 hover:bg-gray-100'" class="rounded px-2.5 py-1.5 text-xs font-semibold">4</button>
                        </div>
                    </div>
                </div>

                @if($products->count())
                    <div
                        class="mt-6 grid gap-6"
                        :class="{
                            'grid-cols-1 xs:grid-cols-2': gridCols === '2',
                            'grid-cols-1 xs:grid-cols-2 md:grid-cols-3': gridCols === '3',
                            'grid-cols-1 xs:grid-cols-2 md:grid-cols-3 lg:grid-cols-4': gridCols === '4',
                        }"
                    >
                        @foreach($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>
                    <div class="mt-10">
                        {{ $products->links('components.pagination') }}
                    </div>
                @else
                    <div class="mt-12 rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
                        <h2 class="text-xl font-semibold text-primary">No products found</h2>
                        <p class="mt-2 text-gray-600">Try adjusting your filters or browse the full catalog.</p>
                        <a href="{{ $emptyClearUrl }}" class="mt-6 inline-flex rounded-lg bg-accent px-6 py-3 text-sm font-semibold text-white hover:bg-accent-dark">
                            Clear filters
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div x-show="mobileFiltersOpen" x-cloak class="fixed inset-0 z-[60] lg:hidden" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-black/50" @click="mobileFiltersOpen = false"></div>
            <aside class="absolute left-0 top-0 flex h-full w-full max-w-sm flex-col bg-white shadow-xl" @click.stop>
                <div class="flex items-center justify-between border-b border-gray-200 px-4 py-4">
                    <h2 class="text-lg font-semibold text-primary">Filters</h2>
                    <button type="button" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100" @click="mobileFiltersOpen = false" aria-label="Close filters">&times;</button>
                </div>
                <div class="flex-1 overflow-y-auto p-4" x-ref="mobileFilters">
                    <x-product-listing-filters
                        id-prefix="mobile-"
                        :hide-brands="$hideBrandFilter"
                        :clear-filters-url="$clearFiltersUrl"
                        :all-brands="$allBrands"
                        :all-categories="$allCategories"
                        :all-colors="$allColors"
                        :all-sizes="$allSizes"
                        :price-floor="$priceFloor"
                        :price-ceiling="$priceCeiling"
                    />
                </div>
                <div class="border-t border-gray-200 p-4">
                    <button type="button" class="w-full rounded-lg bg-accent py-3 text-sm font-semibold text-white hover:bg-accent-dark" @click="mobileFiltersOpen = false; submitFilters()">
                        Apply Filters
                    </button>
                </div>
            </aside>
        </div>
    </form>
</div>
