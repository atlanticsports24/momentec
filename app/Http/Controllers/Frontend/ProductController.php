<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Frontend\Concerns\ProvidesCatalogFilters;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ProvidesCatalogFilters;

    public function index(Request $request)
    {
        $query = Product::with(['brand', 'images', 'variants', 'categories']);

        $this->applyCatalogFilters($query, $request);

        $catalog = $this->catalogViewData($request);

        $activeFilters = array_filter([
            'brand' => $request->brand,
            'category' => $request->category,
            'color' => $request->color,
            'size' => $request->size,
            'min_price' => $request->filled('min_price') && (int) $request->min_price > $catalog['priceFloor']
                ? $request->min_price
                : null,
            'max_price' => $request->filled('max_price') && (int) $request->max_price < $catalog['priceCeiling']
                ? $request->max_price
                : null,
        ], fn ($value) => filled($value));

        $products = $query->paginate(24)->appends($catalog['catalogQuery']);

        return view('pages.product-listing', [
            'products' => $products,
            ...$catalog,
            'activeFilters' => $activeFilters,
            'activeFilterTags' => $this->activeFilterTagsFromFilters(
                $activeFilters,
                $catalog['allBrands'],
                $catalog['allCategories'],
            ),
        ]);
    }

    public function show(Product $product)
    {
        $product->load([
            'brand',
            'images' => fn ($q) => $q->orderBy('sort_order'),
            'variants' => fn ($q) => $q->orderBy('color')->orderBy('size'),
            'variants.images' => fn ($q) => $q->orderBy('sort_order'),
            'categories.parent',
            'variantDisplayOptions',
        ]);

        $related = Product::with(['brand', 'images', 'variants'])
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $product->categories->pluck('id')))
            ->where('products.id', '!=', $product->id)
            ->take(6)
            ->get();

        return view('pages.product-detail', compact('product', 'related'));
    }
}
