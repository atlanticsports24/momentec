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
        if ($request->filled('brand')) {
            $query->whereHas('brand', fn ($q) => $q->where('slug', $request->query('brand')));
        }

        if ($request->filled('category')) {
            $query->whereHas('categories', fn ($q) => $q->where('slug', $request->query('category')));
        }

        if ($request->filled('color')) {
            $query->whereHas('variants', fn ($q) => $q->where('color', $request->query('color')));
        }

        if ($request->filled('size')) {
            $query->whereHas('variants', fn ($q) => $q->where('size', $request->query('size')));
        }

        $priceFloor = (int) floor((float) Product::min('min_msrp') ?: 0);
        $priceCeiling = (int) ceil((float) Product::max('max_msrp') ?: 500);

        if ($request->filled('min_price') && (int) $request->query('min_price') > $priceFloor) {
            $min = (int) $request->query('min_price');
            $query->where(function ($q) use ($min) {
                $q->where('min_msrp', '>=', $min)->orWhereNull('min_msrp');
            });
        }

        if ($request->filled('max_price') && (int) $request->query('max_price') < $priceCeiling) {
            $max = (int) $request->query('max_price');
            $query->where(function ($q) use ($max) {
                $q->where('max_msrp', '<=', $max)->orWhereNull('max_msrp');
            });
        }

        match ($request->query('sort')) {
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

        if ($request->filled('brand')) {
            $query['brand'] = $request->query('brand');
        }

        if ($request->filled('category')) {
            $query['category'] = $request->query('category');
        }

        if ($request->filled('color')) {
            $query['color'] = $request->query('color');
        }

        if ($request->filled('size')) {
            $query['size'] = $request->query('size');
        }

        if ($request->filled('min_price') && (int) $request->query('min_price') > $priceFloor) {
            $query['min_price'] = (int) $request->query('min_price');
        }

        if ($request->filled('max_price') && (int) $request->query('max_price') < $priceCeiling) {
            $query['max_price'] = (int) $request->query('max_price');
        }

        if ($request->filled('sort')) {
            $query['sort'] = $request->query('sort');
        }

        return $query;
    }

    /**
     * @return array<string, string|int>
     */
    protected function activeFilters(Request $request, int $priceFloor, int $priceCeiling): array
    {
        $filters = array_filter([
            'brand' => $request->query('brand'),
            'category' => $request->query('category'),
            'color' => $request->query('color'),
            'size' => $request->query('size'),
        ], fn ($value) => filled($value));

        if ($request->filled('min_price') && (int) $request->query('min_price') > $priceFloor) {
            $filters['min_price'] = (int) $request->query('min_price');
        }

        if ($request->filled('max_price') && (int) $request->query('max_price') < $priceCeiling) {
            $filters['max_price'] = (int) $request->query('max_price');
        }

        return $filters;
    }

    /**
     * @param  array<string, string|int>  $activeFilters
     * @return array<int, array{key: string, label: string}>
     */
    protected function activeFilterTagsFromFilters(array $activeFilters, $allBrands, $allCategories): array
    {
        $tags = [];

        if (isset($activeFilters['brand'])) {
            $brand = $allBrands->firstWhere('slug', $activeFilters['brand']);
            $tags[] = ['key' => 'brand', 'label' => $brand?->name ?? $activeFilters['brand']];
        }

        if (isset($activeFilters['category'])) {
            $cat = $allCategories->flatMap(fn ($p) => collect([$p])->merge($p->children))->firstWhere('slug', $activeFilters['category']);
            $tags[] = ['key' => 'category', 'label' => $cat?->name ?? $activeFilters['category']];
        }

        if (isset($activeFilters['color'])) {
            $tags[] = ['key' => 'color', 'label' => $activeFilters['color']];
        }

        if (isset($activeFilters['size'])) {
            $tags[] = ['key' => 'size', 'label' => $activeFilters['size']];
        }

        if (isset($activeFilters['min_price'])) {
            $tags[] = ['key' => 'min_price', 'label' => 'Min $'.number_format((int) $activeFilters['min_price'], 0)];
        }

        if (isset($activeFilters['max_price'])) {
            $tags[] = ['key' => 'max_price', 'label' => 'Max $'.number_format((int) $activeFilters['max_price'], 0)];
        }

        return $tags;
    }

    /**
     * @return array{allBrands: \Illuminate\Support\Collection, allCategories: \Illuminate\Support\Collection, allColors: \Illuminate\Support\Collection, allSizes: \Illuminate\Support\Collection, priceFloor: int, priceCeiling: int, activeFilters: array<string, string|int>, activeFilterTags: array<int, array{key: string, label: string}>, catalogQuery: array<string, mixed>}
     */
    protected function catalogViewData(Request $request): array
    {
        $data = $this->catalogFilterData();
        $activeFilters = $this->activeFilters($request, $data['priceFloor'], $data['priceCeiling']);

        return [
            ...$data,
            'activeFilters' => $activeFilters,
            'activeFilterTags' => $this->activeFilterTagsFromFilters(
                $activeFilters,
                $data['allBrands'],
                $data['allCategories'],
            ),
            'catalogQuery' => $this->catalogQueryParams(
                $request,
                $data['priceFloor'],
                $data['priceCeiling'],
            ),
        ];
    }
}
