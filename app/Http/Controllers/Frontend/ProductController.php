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

        $products = $query->paginate(24)->appends($catalog['catalogQuery']);

        return view('pages.product-listing', [
            'products' => $products,
            ...$catalog,
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
