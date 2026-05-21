<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Frontend\Concerns\ProvidesCatalogFilters;
use App\Models\Brand;
use App\Models\Product;
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

        $products = $query->paginate(24)->appends($catalog['catalogQuery']);

        return view('pages.brand', [
            'brand' => $brand,
            'products' => $products,
            ...$catalog,
        ]);
    }
}
