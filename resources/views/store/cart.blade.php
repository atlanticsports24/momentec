@extends('layouts.app')

@section('title', 'Your Cart')

@push('styles')
<style>
.cart-wrap { max-width:1280px; margin:0 auto; padding:32px 24px 60px; }
.cart-grid { display:grid; grid-template-columns:1fr 360px; gap:28px; align-items:start; }
@media(max-width:1024px) { .cart-grid { grid-template-columns:1fr; } }

.cart-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; }
.cart-title { font-size:1.6rem; font-weight:900; color:#111827; }
.cart-count { font-size:14px; color:#9ca3af; font-weight:500; }

.cart-items { display:flex; flex-direction:column; gap:12px; }
.cart-item {
    background:#fff; border:1.5px solid #e5e7eb; border-radius:16px;
    padding:16px 20px; display:grid;
    grid-template-columns:72px 1fr auto auto;
    gap:16px; align-items:center;
}
@media(max-width:640px) { .cart-item { grid-template-columns:60px 1fr; gap:12px; } }
.cart-item-img { width:72px; height:72px; border-radius:10px; overflow:hidden; background:#f8fafc; border:1px solid #e5e7eb; }
.cart-item-img img { width:100%; height:100%; object-fit:contain; }
.cart-item-name { font-size:14px; font-weight:700; color:#111827; line-height:1.4; }
.cart-item-meta { font-size:12px; color:#9ca3af; margin-top:3px; }
.cart-item-brand { font-size:11px; font-weight:700; text-transform:uppercase; color:#4f46e5; margin-bottom:3px; }
.cart-qty { display:flex; align-items:center; border:1.5px solid #e5e7eb; border-radius:10px; overflow:hidden; }
.cart-qty-btn { width:32px; height:36px; border:none; background:none; font-size:16px; cursor:pointer; color:#374151; transition:background .15s; }
.cart-qty-btn:hover { background:#f3f4f6; }
.cart-qty-input { width:40px; text-align:center; border:none; border-left:1px solid #e5e7eb; border-right:1px solid #e5e7eb; font-size:13px; font-weight:700; color:#111827; outline:none; height:36px; }
.cart-item-price { font-size:15px; font-weight:800; color:#111827; white-space:nowrap; }
.cart-remove { background:none; border:none; color:#d1d5db; cursor:pointer; padding:4px; border-radius:6px; transition:color .15s; }
.cart-remove:hover { color:#ef4444; }

.cart-summary { background:#fff; border:1.5px solid #e5e7eb; border-radius:20px; padding:24px; position:sticky; top:80px; }
.cart-summary-title { font-size:16px; font-weight:800; color:#111827; margin-bottom:20px; }
.cart-summary-row { display:flex; justify-content:space-between; font-size:14px; padding:8px 0; border-bottom:1px solid #f1f5f9; }
.cart-summary-row:last-of-type { border-bottom:none; }
.cart-summary-total { display:flex; justify-content:space-between; font-size:17px; font-weight:900; color:#111827; padding-top:16px; margin-top:4px; border-top:2px solid #e5e7eb; }
.checkout-btn {
    display:block; width:100%; background:#4f46e5; color:#fff; border:none;
    border-radius:14px; padding:15px; font-size:15px; font-weight:700;
    text-align:center; text-decoration:none; cursor:pointer;
    transition:all .2s; margin-top:16px;
}
.checkout-btn:hover { background:#4338ca; transform:translateY(-1px); box-shadow:0 6px 20px rgba(79,70,229,.3); }
.continue-btn { display:block; text-align:center; font-size:13px; font-weight:600; color:#6b7280; text-decoration:none; margin-top:12px; }
.continue-btn:hover { color:#4f46e5; }

.cart-empty { text-align:center; padding:80px 24px; background:#fff; border-radius:20px; border:1.5px solid #e5e7eb; }
</style>
@endpush

@section('content')
@php $totalItems = $lines->sum('quantity'); @endphp

<div class="cart-wrap">
    <div class="cart-header">
        <div>
            <div class="cart-title">Your Cart</div>
            <div class="cart-count">{{ $totalItems }} {{ Str::plural('item', $totalItems) }}</div>
        </div>
        <a href="{{ route('products.index') }}" style="font-size:13px;font-weight:600;color:#4f46e5;text-decoration:none;">← Continue Shopping</a>
    </div>

    @if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:12px;padding:12px 16px;margin-bottom:20px;font-size:14px;font-weight:600;color:#065f46;">
        ✅ {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:12px 16px;margin-bottom:20px;font-size:14px;font-weight:600;color:#dc2626;">
        {{ session('error') }}
    </div>
    @endif

    @if($lines->isEmpty())
    <div class="cart-empty">
        <svg style="width:64px;height:64px;color:#d1d5db;margin:0 auto 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <div style="font-size:18px;font-weight:800;color:#111827;margin-bottom:8px;">Your cart is empty</div>
        <div style="font-size:14px;color:#9ca3af;margin-bottom:24px;">Browse products and add items to get started.</div>
        <a href="{{ route('products.index') }}" style="display:inline-flex;background:#4f46e5;color:#fff;padding:12px 28px;border-radius:12px;font-size:14px;font-weight:700;text-decoration:none;">
            Shop All Products
        </a>
    </div>
    @else
    <div class="cart-grid">
        <div class="cart-items">
            @foreach($lines as $line)
            @php
                $variant = $line['variant'];
                $product = $line['product'];
                $img = $product?->mainImageUrl() ?? '';
                $placeholder = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iNDAwIj48cmVjdCB3aWR0aD0iNDAwIiBoZWlnaHQ9IjQwMCIgZmlsbD0iI2Y4ZmFmYyIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmb250LXNpemU9IjEyIiBmaWxsPSIjOWNhM2FmIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
            @endphp
            <div class="cart-item">
                <div class="cart-item-img">
                    @if($product && $img)
                    <a href="{{ route('products.show', $product) }}">
                        <img src="{{ $img }}" alt="{{ $product->name }}" onerror="this.src='{{ $placeholder }}'">
                    </a>
                    @else
                    <img src="{{ $placeholder }}" alt="">
                    @endif
                </div>

                <div>
                    @if($product?->brand)
                    <div class="cart-item-brand">{{ $product->brand->name }}</div>
                    @endif
                    <div class="cart-item-name">
                        @if($product)
                        <a href="{{ route('products.show', $product) }}" style="text-decoration:none;color:inherit;">{{ html_entity_decode($product->name, ENT_QUOTES, 'UTF-8') }}</a>
                        @else
                        {{ $variant->item_sku }}
                        @endif
                    </div>
                    <div class="cart-item-meta">
                        {{ $variant->color }} @if($variant->color && $variant->size) / @endif {{ $variant->size }}
                        &bull; SKU: {{ $variant->item_sku }}
                    </div>
                </div>

                <div x-data="{ qty: {{ $line['quantity'] }} }">
                    <form action="{{ route('store.cart.update') }}" method="POST" id="qty-form-{{ $variant->id }}">
                        @csrf
                        <input type="hidden" name="variant_id" value="{{ $variant->id }}">
                        <input type="hidden" name="quantity" :value="qty" id="qty-val-{{ $variant->id }}">
                    </form>

                    <div class="cart-qty">
                        <button type="button" class="cart-qty-btn"
                            @click="if(qty > 1) { qty--; $nextTick(() => document.getElementById('qty-form-{{ $variant->id }}').submit()) }
                                    else if(qty === 1) { if(confirm('Remove this item?')) { qty=0; $nextTick(() => document.getElementById('qty-form-{{ $variant->id }}').submit()) } }">
                            −
                        </button>
                        <span style="width:40px;text-align:center;font-size:14px;font-weight:700;color:#111827;display:flex;align-items:center;justify-content:center;"
                              x-text="qty">
                        </span>
                        <button type="button" class="cart-qty-btn"
                            @click="if(qty < 99) { qty++; $nextTick(() => document.getElementById('qty-form-{{ $variant->id }}').submit()) }">
                            +
                        </button>
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;">
                    <div class="cart-item-price">${{ number_format($line['total'], 2) }}</div>
                    <form action="{{ route('store.cart.remove', $variant) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="cart-remove" title="Remove">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        <div class="cart-summary">
            <div class="cart-summary-title">Order Summary</div>
            <div class="cart-summary-row">
                <span style="color:#6b7280;">Subtotal ({{ $totalItems }} items)</span>
                <span style="font-weight:700;">${{ number_format($subtotal, 2) }}</span>
            </div>
            <div class="cart-summary-row">
                <span style="color:#6b7280;">Shipping</span>
                <span style="color:#059669;font-weight:600;">Calculated at checkout</span>
            </div>
            <div class="cart-summary-total">
                <span>Estimated Total</span>
                <span>${{ number_format($subtotal, 2) }}</span>
            </div>
            <a href="{{ route('store.checkout') }}" class="checkout-btn">
                Proceed to Checkout →
            </a>
            <a href="{{ route('products.index') }}" class="continue-btn">
                ← Continue Shopping
            </a>
            <div style="margin-top:20px;padding-top:16px;border-top:1px solid #f1f5f9;display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:#6b7280;">
                    <svg width="14" height="14" fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Free shipping on orders over $150
                </div>
                <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:#6b7280;">
                    <svg width="14" height="14" fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Easy returns policy
                </div>
                <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:#6b7280;">
                    <svg width="14" height="14" fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Bulk pricing available
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
