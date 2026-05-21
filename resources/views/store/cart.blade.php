@extends('layouts.store')

@section('title', 'Cart')

@section('content')
<h1 class="text-2xl font-bold mb-6">Shopping cart</h1>
@if ($lines->isEmpty())
    <p class="text-gray-600">Your cart is empty. <a href="{{ route('store.shop') }}" class="text-blue-600 underline">Continue shopping</a></p>
@else
    <div class="bg-white border rounded-lg divide-y">
        @foreach ($lines as $line)
            <div class="p-4 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="font-medium">{{ $line['product']?->name ?? $line['variant']->item_sku }}</div>
                    <div class="text-sm text-gray-500">{{ $line['variant']->item_sku }}</div>
                </div>
                <form action="{{ route('store.cart.update') }}" method="post" class="flex items-center gap-2">
                    @csrf
                    <input type="hidden" name="variant_id" value="{{ $line['variant']->id }}">
                    <input type="number" name="quantity" value="{{ $line['quantity'] }}" min="0" max="99" class="w-16 border rounded px-2 py-1">
                    <button type="submit" class="text-sm text-blue-600">Update</button>
                </form>
                <div class="font-medium">${{ number_format($line['total'], 2) }}</div>
            </div>
        @endforeach
    </div>
    <div class="mt-6 flex justify-between items-center">
        <span class="text-lg font-semibold">Subtotal: ${{ number_format($subtotal, 2) }}</span>
        <a href="{{ route('store.checkout') }}" class="bg-blue-600 text-white px-5 py-2.5 rounded-lg hover:bg-blue-700">Checkout</a>
    </div>
@endif
@endsection
