<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::with(['brand', 'images', 'variants'])
            ->whereHas('variants')
            ->latest()
            ->take(12)
            ->get();

        $brands = Brand::withCount('products')
            ->orderBy('products_count', 'desc')
            ->take(16)
            ->get();

        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        return view('pages.home', compact('featuredProducts', 'brands', 'categories'));
    }

    public function newsletter(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        session([
            'newsletter_email' => $validated['email'],
            'newsletter_subscribed' => true,
        ]);

        return redirect()->route('home')->with('newsletter_success', 'Thanks for subscribing! We\'ll keep you updated.');
    }
}
