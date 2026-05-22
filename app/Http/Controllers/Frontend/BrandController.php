<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Frontend\Concerns\ProvidesCatalogFilters;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    use ProvidesCatalogFilters;

    public function index()
    {
        $brands = Brand::withCount('products')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Brand $brand) => strtoupper(substr($brand->name, 0, 1)) ?: '#');

        $letters = $brands->keys()->sort()->values();

        return view('pages.brands-all', compact('brands', 'letters'));
    }

    public function show(Brand $brand, Request $request)
    {
        $brand->loadCount('products');

        $query = Product::with(['brand', 'images', 'variants', 'categories'])
            ->where('brand_id', $brand->id);

        $this->applyCatalogFilters($query, $request);

        $catalog = $this->catalogViewData($request);

        $allCategories = Category::query()
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->withCount(['products' => fn ($pq) => $pq->where('brand_id', $brand->id)])->orderBy('name')])
            ->withCount(['products' => fn ($q) => $q->where('brand_id', $brand->id)])
            ->orderBy('name')
            ->get();

        $allColors = ProductVariant::query()
            ->whereHas('product', fn ($q) => $q->where('brand_id', $brand->id))
            ->whereNotNull('color')
            ->where('color', '!=', '')
            ->selectRaw('color, MAX(color_hex_value) as color_hex_value')
            ->groupBy('color')
            ->orderBy('color')
            ->get();

        $allSizes = ProductVariant::query()
            ->whereHas('product', fn ($q) => $q->where('brand_id', $brand->id))
            ->whereNotNull('size')
            ->where('size', '!=', '')
            ->distinct()
            ->orderBy('size')
            ->pluck('size')
            ->filter()
            ->values();

        $products = $query->paginate(24)->appends($catalog['catalogQuery']);

        return view('pages.brand', [
            'brand' => $brand,
            'products' => $products,
            ...$catalog,
            'allCategories' => $allCategories,
            'allColors' => $allColors,
            'allSizes' => $allSizes,
        ]);
    }
}
