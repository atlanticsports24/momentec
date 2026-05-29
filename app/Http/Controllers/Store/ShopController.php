<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function show(Product $product): View
    {
        $product->load(['brand', 'variants', 'variantDisplayOptions']);

        return view('store.product', compact('product'));
    }

    public function variant(ProductVariant $variant): View
    {
        $variant->load('product.brand');

        return view('store.variant', compact('variant'));
    }
}
