<?php

namespace App\Http\Controllers\Frontend\Concerns;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ProvidesCatalogFilters
{
    protected function applyCatalogFilters(Builder $query, Request $request): void
    {
        $brands = array_filter((array) $request->input('brands', $request->brand ? [$request->brand] : []));
        if ($brands !== []) {
            $query->whereHas('brand', fn ($q) => $q->whereIn('slug', $brands));
        }

        $categories = array_filter((array) $request->input('categories', $request->category ? [$request->category] : []));
        if ($categories !== []) {
            $query->whereHas('categories', fn ($q) => $q->whereIn('slug', $categories));
        }

        $colors = array_filter((array) $request->input('colors', $request->color ? [$request->color] : []));
        if ($colors !== []) {
            $query->whereHas('variants', fn ($q) => $q->whereIn('color', $colors));
        }

        $sizes = array_filter((array) $request->input('sizes', $request->size ? [$request->size] : []));
        if ($sizes !== []) {
            $query->whereHas('variants', fn ($q) => $q->whereIn('size', $sizes));
        }

        $priceFloor = (int) floor((float) Product::min('min_msrp') ?: 0);
        $priceCeiling = (int) ceil((float) Product::max('max_msrp') ?: 500);

        if ($request->filled('min_price') && (int) $request->min_price > $priceFloor) {
            $min = (int) $request->min_price;
            $query->where(function ($q) use ($min) {
                $q->where('min_msrp', '>=', $min)->orWhereNull('min_msrp');
            });
        }

        if ($request->filled('max_price') && (int) $request->max_price < $priceCeiling) {
            $max = (int) $request->max_price;
            $query->where(function ($q) use ($max) {
                $q->where('max_msrp', '<=', $max)->orWhereNull('max_msrp');
            });
        }

        match ($request->sort) {
            'price_asc' => $query->orderBy('min_msrp', 'asc'),
            'price_desc' => $query->orderBy('min_msrp', 'desc'),
            'newest' => $query->orderBy('launch_date', 'desc'),
            default => $query->latest(),
        };
    }

    /**
     * @return array{allBrands: \Illuminate\Support\Collection, allCategories: \Illuminate\Support\Collection, allColors: \Illuminate\Support\Collection, allSizes: \Illuminate\Support\Collection, priceFloor: int, priceCeiling: int}
     */
    protected function catalogFilterData(): array
    {
        return [
            'allBrands' => Brand::withCount('products')->orderBy('name')->get(),
            'allCategories' => Category::query()
                ->whereNull('parent_id')
                ->with(['children' => fn ($q) => $q->withCount('products')->orderBy('name')])
                ->withCount('products')
                ->orderBy('name')
                ->get(),
            'allColors' => ProductVariant::query()
                ->whereNotNull('color')
                ->where('color', '!=', '')
                ->selectRaw('color, MAX(color_hex_value) as color_hex_value')
                ->groupBy('color')
                ->orderBy('color')
                ->get(),
            'allSizes' => ProductVariant::query()
                ->whereNotNull('size')
                ->where('size', '!=', '')
                ->distinct()
                ->orderBy('size')
                ->pluck('size'),
            'priceFloor' => (int) floor((float) Product::min('min_msrp') ?: 0),
            'priceCeiling' => (int) ceil((float) Product::max('max_msrp') ?: 500),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function catalogQueryParams(Request $request, int $priceFloor, int $priceCeiling): array
    {
        $query = [];

        $brands = array_values(array_filter((array) $request->input('brands', $request->brand ? [$request->brand] : [])));
        if ($brands !== []) {
            $query['brands'] = $brands;
        }

        $categories = array_values(array_filter((array) $request->input('categories', $request->category ? [$request->category] : [])));
        if ($categories !== []) {
            $query['categories'] = $categories;
        }

        $colors = array_values(array_filter((array) $request->input('colors', $request->color ? [$request->color] : [])));
        if ($colors !== []) {
            $query['colors'] = $colors;
        }

        $sizes = array_values(array_filter((array) $request->input('sizes', $request->size ? [$request->size] : [])));
        if ($sizes !== []) {
            $query['sizes'] = $sizes;
        }

        if ($request->filled('min_price') && (int) $request->min_price > $priceFloor) {
            $query['min_price'] = (int) $request->min_price;
        }

        if ($request->filled('max_price') && (int) $request->max_price < $priceCeiling) {
            $query['max_price'] = (int) $request->max_price;
        }

        if ($request->filled('sort')) {
            $query['sort'] = $request->sort;
        }

        return $query;
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{key: string, value: string, label: string}>
     */
    protected function buildActiveFilterTags(Request $request, $allBrands, $allCategories, int $priceFloor, int $priceCeiling): \Illuminate\Support\Collection
    {
        $tags = collect();

        foreach (array_filter((array) $request->input('brands', $request->brand ? [$request->brand] : [])) as $slug) {
            $brand = $allBrands->firstWhere('slug', $slug);
            $tags->push(['key' => 'brands', 'value' => $slug, 'label' => $brand?->name ?? $slug]);
        }

        foreach (array_filter((array) $request->input('categories', $request->category ? [$request->category] : [])) as $slug) {
            $cat = $allCategories->flatMap(fn ($p) => collect([$p])->merge($p->children))->firstWhere('slug', $slug);
            $tags->push(['key' => 'categories', 'value' => $slug, 'label' => $cat?->name ?? $slug]);
        }

        foreach (array_filter((array) $request->input('colors', $request->color ? [$request->color] : [])) as $color) {
            $tags->push(['key' => 'colors', 'value' => $color, 'label' => $color]);
        }

        foreach (array_filter((array) $request->input('sizes', $request->size ? [$request->size] : [])) as $size) {
            $tags->push(['key' => 'sizes', 'value' => $size, 'label' => $size]);
        }

        if ($request->filled('min_price') && (int) $request->min_price > $priceFloor) {
            $tags->push(['key' => 'min_price', 'value' => (string) $request->min_price, 'label' => 'Min $'.number_format((int) $request->min_price, 0)]);
        }

        if ($request->filled('max_price') && (int) $request->max_price < $priceCeiling) {
            $tags->push(['key' => 'max_price', 'value' => (string) $request->max_price, 'label' => 'Max $'.number_format((int) $request->max_price, 0)]);
        }

        return $tags;
    }

    /**
     * @return array{allBrands: \Illuminate\Support\Collection, allCategories: \Illuminate\Support\Collection, allColors: \Illuminate\Support\Collection, allSizes: \Illuminate\Support\Collection, priceFloor: int, priceCeiling: int, activeFilterTags: \Illuminate\Support\Collection, catalogQuery: array<string, mixed>}
     */
    protected function catalogViewData(Request $request): array
    {
        $data = $this->catalogFilterData();

        return [
            ...$data,
            'activeFilterTags' => $this->buildActiveFilterTags(
                $request,
                $data['allBrands'],
                $data['allCategories'],
                $data['priceFloor'],
                $data['priceCeiling'],
            ),
            'catalogQuery' => $this->catalogQueryParams(
                $request,
                $data['priceFloor'],
                $data['priceCeiling'],
            ),
        ];
    }
}
