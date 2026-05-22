@props([
    'allBrands',
    'allCategories',
    'allColors',
    'allSizes',
    'priceFloor' => 0,
    'priceCeiling' => 500,
    'idPrefix' => '',
    'hideBrands' => false,
    'hideCategories' => false,
    'clearFiltersUrl' => null,
])

@php
    $selectedBrand = request()->query('brand');
    $selectedCategory = request()->query('category');
    $selectedColor = request()->query('color');
    $selectedSize = request()->query('size');
    $currentMin = request()->query('min_price', $priceFloor);
    $currentMax = request()->query('max_price', $priceCeiling);
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
                                type="radio"
                                name="brand"
                                value="{{ $brand->slug }}"
                                @checked($selectedBrand === $brand->slug)
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

    @unless($hideCategories)
    <div class="filter-group">
        <h3 class="filter-group-title">Category</h3>
        <ul style="list-style:none;margin:0;padding:0;max-height:224px;overflow-y:auto;">
            @foreach($allCategories as $parent)
                <li>
                    <label class="filter-item">
                        <span class="filter-item-label">
                            <input
                                type="radio"
                                name="category"
                                value="{{ $parent->slug }}"
                                @checked($selectedCategory === $parent->slug)
                            >
                            {{ $parent->name }}
                        </span>
                        <span class="filter-item-count">{{ $parent->products_count }}</span>
                    </label>
                    @foreach($parent->children as $child)
                        <label class="filter-item" style="padding-left:20px;">
                            <span class="filter-item-label">
                                <input
                                    type="radio"
                                    name="category"
                                    value="{{ $child->slug }}"
                                    @checked($selectedCategory === $child->slug)
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
    @endunless

    @if($allColors->count())
        <div class="filter-group" x-data="{ showAll: false }">
            <h3 class="filter-group-title">Color</h3>
            <div class="filter-colors" :style="showAll ? '' : 'max-height:200px;overflow:hidden'">
                @foreach($allColors as $colorOption)
                    @php $isSelected = $selectedColor === $colorOption->color; @endphp
                    <label
                        title="{{ $colorOption->color }}"
                        style="cursor:pointer;"
                        x-show="showAll || {{ $loop->index }} < 40"
                    >
                        <input
                            type="radio"
                            name="color"
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
            @if($allColors->count() > 40)
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
                    @php $sizeSelected = $selectedSize === $size; @endphp
                    <label style="cursor:pointer;">
                        <input
                            type="radio"
                            name="size"
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
