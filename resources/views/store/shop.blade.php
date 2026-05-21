@extends('layouts.store')

@section('title', 'Shop')

@section('content')
<h1 class="text-2xl font-bold mb-6">Shop</h1>
@if ($products->isEmpty())
    <p class="text-gray-600">No products yet. Run a catalog sync from the admin Sync Center.</p>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($products as $product)
            <a href="{{ route('store.product', $product) }}" class="block bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition">
                @if ($url = $product->mainImageUrl())
                    <img src="{{ $url }}" alt="" class="w-full h-48 object-cover">
                @else
                    <div class="w-full h-48 bg-gray-100 flex items-center justify-center text-gray-400">No image</div>
                @endif
                <div class="p-4">
                    <h2 class="font-medium">{{ $product->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $product->parent_sku }}</p>
                    @if ($product->min_msrp)
                        <p class="mt-2 text-sm">From ${{ number_format($product->min_msrp, 2) }}</p>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection
