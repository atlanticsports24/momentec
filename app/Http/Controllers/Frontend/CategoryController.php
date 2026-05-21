<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Frontend\Concerns\ProvidesCatalogFilters;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ProvidesCatalogFilters;

    public function index()
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->withCount('products')->orderBy('name')])
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return view('pages.categories-all', compact('categories'));
    }

    public function show(Category $category, Request $request)
    {
        $category->load([
            'parent',
            'children' => fn ($q) => $q->withCount('products')->orderBy('name'),
        ])->loadCount('products');

        $categoryIds = collect([$category->id])->merge($category->children->pluck('id'));

        $query = Product::with(['brand', 'images', 'variants', 'categories'])
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds));

        $this->applyCatalogFilters($query, $request);

        $catalog = $this->catalogViewData($request);

        $products = $query->paginate(24)->appends($catalog['catalogQuery']);

        return view('pages.category', [
            'category' => $category,
            'products' => $products,
            ...$catalog,
        ]);
    }
}
