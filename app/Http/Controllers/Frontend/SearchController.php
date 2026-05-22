<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->input('q', ''));

        if ($request->boolean('ajax')) {
            $products = Product::with(['brand', 'images', 'variants'])
                ->where(function ($query) use ($q) {
                    $query->where('name', 'LIKE', "%{$q}%")
                        ->orWhere('description', 'LIKE', "%{$q}%")
                        ->orWhereHas('brand', fn ($b) => $b->where('name', 'LIKE', "%{$q}%"));
                })
                ->take(8)
                ->get();

            return response()->json([
                'query' => $q,
                'products' => $products->map(fn (Product $product) => [
                    'name' => $product->name,
                    'brand' => $product->brand?->name,
                    'url' => route('products.show', $product),
                    'image' => $product->mainImageUrl() ?? asset('images/placeholder.jpg'),
                ]),
            ]);
        }

        $products = Product::with(['brand', 'images', 'variants'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'LIKE', "%{$q}%")
                        ->orWhere('description', 'LIKE', "%{$q}%")
                        ->orWhereHas('brand', fn ($b) => $b->where('name', 'LIKE', "%{$q}%"));
                });
            })
            ->latest()
            ->paginate(24)
            ->withQueryString();

        $suggestedBrands = Brand::withCount('products')
            ->orderByDesc('products_count')
            ->take(6)
            ->get();

        $suggestedCategories = Category::query()
            ->whereNull('parent_id')
            ->withCount('products')
            ->orderByDesc('products_count')
            ->take(6)
            ->get();

        return view('pages.search', compact('products', 'q', 'suggestedBrands', 'suggestedCategories'));
    }
}
