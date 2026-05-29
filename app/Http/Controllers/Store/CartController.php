<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Services\Store\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index()
    {
        return view('store.cart', [
            'lines' => $this->cart->lines(),
            'subtotal' => $this->cart->subtotal(),
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $this->cart->add(
            (int) $validated['variant_id'],
            (int) ($validated['quantity'] ?? 1)
        );

        return redirect()->route('store.cart')->with('success', 'Added to cart.');
    }

    public function addBulk(Request $request): RedirectResponse
    {
        $items = $request->input('items', []);

        \Log::info('addBulk received:', ['items' => $items, 'all' => $request->all()]);

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $variantId = (int) ($item['variant_id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);

            if ($variantId > 0 && $quantity > 0) {
                $this->cart->add($variantId, $quantity);
            }
        }

        $count = $this->cart->count();

        if ($count === 0) {
            return redirect()->route('store.cart')
                ->with('error', 'No items were added. Please select a color and enter quantities.');
        }

        return redirect()->route('store.cart')
            ->with('success', $count.' items added to cart!');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $this->cart->update((int) $validated['variant_id'], (int) $validated['quantity']);

        return redirect()->route('store.cart');
    }

    public function remove(ProductVariant $variant): RedirectResponse
    {
        $this->cart->remove($variant->id);

        return redirect()->route('store.cart');
    }
}
