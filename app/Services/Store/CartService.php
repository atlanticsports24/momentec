<?php

namespace App\Services\Store;

use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class CartService
{
    private const SESSION_KEY = 'cart';

    public function items(): Collection
    {
        return collect(session(self::SESSION_KEY, []));
    }

    public function add(int $variantId, int $quantity = 1): void
    {
        $cart = session(self::SESSION_KEY, []);
        $key = (string) $variantId;
        $cart[$key] = ($cart[$key] ?? 0) + $quantity;
        session([self::SESSION_KEY => $cart]);
    }

    public function update(int $variantId, int $quantity): void
    {
        $cart = session(self::SESSION_KEY, []);
        $key = (string) $variantId;

        if ($quantity <= 0) {
            unset($cart[$key]);
        } else {
            $cart[$key] = $quantity;
        }

        session([self::SESSION_KEY => $cart]);
    }

    public function remove(int $variantId): void
    {
        $cart = session(self::SESSION_KEY, []);
        unset($cart[(string) $variantId]);
        session([self::SESSION_KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function count(): int
    {
        return (int) $this->items()->sum();
    }

    public function lines(): Collection
    {
        $cart = $this->items();

        if ($cart->isEmpty()) {
            return collect();
        }

        $variants = ProductVariant::query()
            ->with('product')
            ->whereIn('id', $cart->keys()->map(fn ($k) => (int) $k))
            ->get()
            ->keyBy('id');

        return $cart->map(function (int $qty, string $variantId) use ($variants) {
            $variant = $variants->get((int) $variantId);
            if (! $variant) {
                return null;
            }

            $price = (float) ($variant->msrp ?? $variant->product?->min_msrp ?? 0);

            return [
                'variant' => $variant,
                'product' => $variant->product,
                'quantity' => $qty,
                'price' => $price,
                'total' => $price * $qty,
            ];
        })->filter()->values();
    }

    public function subtotal(): float
    {
        return (float) $this->lines()->sum('total');
    }
}
