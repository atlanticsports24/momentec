@props([
    'allBrands',
    'allCategories',
    'allColors',
    'allSizes',
    'priceFloor' => 0,
    'priceCeiling' => 500,
    'idPrefix' => '',
    'hideBrands' => false,
    'clearFiltersUrl' => null,
])

@php
    $selectedBrands = array_filter((array) request('brands', request('brand') ? [request('brand')] : []));
    $selectedCategories = array_filter((array) request('categories', request('category') ? [request('category')] : []));
    $selectedColors = array_filter((array) request('colors', request('color') ? [request('color')] : []));
    $selectedSizes = array_filter((array) request('sizes', request('size') ? [request('size')] : []));
    $currentMin = request('min_price', $priceFloor);
    $currentMax = request('max_price', $priceCeiling);
    $clearFiltersUrl = $clearFiltersUrl ?? route('products.index');
@endphp

<div>
    @unless($hideBrands)
    <div class="filter-group">
        <h3 class="filter-group-title">Brand</h3>
        <ul style="list-style:none;margin:0;padding:0;max-height:192px;overflow-y:auto;">
            @foreach($allBrands as $brand)
                <li>
                    <label class="filter-item">
                        <span class="filter-item-label">
                            <input
                                type="checkbox"
                                name="brands[]"
                                value="{{ $brand->slug }}"
                                @checked(in_array($brand->slug, $selectedBrands, true))
                            >
                            {{ $brand->name }}
                        </span>
                        <span class="filter-item-count">{{ $brand->products_count }}</span>
                    </label>
                </li>
            @endforeach
        </ul>
    </div>
    @endunless

    <div class="filter-group">
        <h3 class="filter-group-title">Category</h3>
        <ul style="list-style:none;margin:0;padding:0;max-height:224px;overflow-y:auto;">
            @foreach($allCategories as $parent)
                <li>
                    <label class="filter-item">
                        <span class="filter-item-label">
                            <input
                                type="checkbox"
                                name="categories[]"
                                value="{{ $parent->slug }}"
                                @checked(in_array($parent->slug, $selectedCategories, true))
                            >
                            {{ $parent->name }}
                        </span>
                        <span class="filter-item-count">{{ $parent->products_count }}</span>
                    </label>
                    @foreach($parent->children as $child)
                        <label class="filter-item" style="padding-left:20px;">
                            <span class="filter-item-label">
                                <input
                                    type="checkbox"
                                    name="categories[]"
                                    value="{{ $child->slug }}"
                                    @checked(in_array($child->slug, $selectedCategories, true))
                                >
                                {{ $child->name }}
                            </span>
                            <span class="filter-item-count">{{ $child->products_count }}</span>
                        </label>
                    @endforeach
                </li>
            @endforeach
        </ul>
    </div>

    @if($allColors->count())
        <div class="filter-group" x-data="{ showAll: false }">
            <h3 class="filter-group-title">Color</h3>
            <div class="filter-colors" :style="showAll ? '' : 'max-height:200px;overflow:hidden'">
                @foreach($allColors as $colorOption)
                    @php $isSelected = in_array($colorOption->color, $selectedColors, true); @endphp
                    <label title="{{ $colorOption->color }}" style="cursor:pointer;">
                        <input
                            type="checkbox"
                            name="colors[]"
                            value="{{ $colorOption->color }}"
                            @checked($isSelected)
                            style="position:absolute;opacity:0;width:0;height:0;"
                        >
                        <span
                            class="filter-swatch {{ $isSelected ? 'active' : '' }}"
                            style="background-color: {{ $colorOption->color_hex_value ?: '#d1d5db' }}"
                        ></span>
                    </label>
                @endforeach
            </div>
            @if($allColors->count() > 12)
                <button
                    type="button"
                    @click="showAll = !showAll"
                    style="font-size:12px;color:#4f46e5;margin-top:8px;background:none;border:none;cursor:pointer;padding:0;"
                >
                    <span x-text="showAll ? 'Show less ↑' : 'Show all colors ↓'"></span>
                </button>
            @endif
        </div>
    @endif

    @if($allSizes->count())
        <div class="filter-group">
            <h3 class="filter-group-title">Size</h3>
            <div class="filter-sizes">
                @foreach($allSizes as $size)
                    @php $sizeSelected = in_array($size, $selectedSizes, true); @endphp
                    <label style="cursor:pointer;">
                        <input
                            type="checkbox"
                            name="sizes[]"
                            value="{{ $size }}"
                            @checked($sizeSelected)
                            style="position:absolute;opacity:0;width:0;height:0;"
                        >
                        <span class="filter-size-pill {{ $sizeSelected ? 'active' : '' }}">{{ $size }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    <div
        class="filter-group"
        x-data="{
            min: {{ (int) $currentMin }},
            max: {{ (int) $currentMax }},
            floor: {{ $priceFloor }},
            ceiling: {{ $priceCeiling }},
        }"
    >
        <h3 class="filter-group-title">Price</h3>
        <p style="font-size:14px;font-weight:600;color:#374151;margin:0 0 12px;">
            $<span x-text="min"></span> — $<span x-text="max"></span>
        </p>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <input
                type="range"
                id="{{ $idPrefix }}min_price"
                name="min_price"
                x-model.number="min"
                :min="floor"
                :max="ceiling"
                style="width:100%;accent-color:#4f46e5;"
                @change="$el.closest('form')?.requestSubmit()"
            >
            <input
                type="range"
                id="{{ $idPrefix }}max_price"
                name="max_price"
                x-model.number="max"
                :min="floor"
                :max="ceiling"
                style="width:100%;accent-color:#4f46e5;"
                @change="$el.closest('form')?.requestSubmit()"
            >
        </div>
    </div>

    <a
        href="{{ $clearFiltersUrl }}"
        style="display:flex;align-items:center;justify-content:center;width:100%;padding:12px;border:1.5px solid #e5e7eb;border-radius:12px;font-size:14px;font-weight:600;color:#374151;text-decoration:none;margin-top:8px;"
    >
        Clear All
    </a>
</div>
