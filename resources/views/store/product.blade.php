@extends('layouts.store')

@section('title', $product->name)

@section('content')
<div class="grid md:grid-cols-2 gap-8">
    <div>
        @if ($url = $product->mainImageUrl())
            <img src="{{ $url }}" alt="" class="rounded-lg border w-full max-h-96 object-cover">
        @endif
    </div>
    <div>
        <h1 class="text-2xl font-bold">{{ $product->name }}</h1>
        <p class="text-gray-500 mt-1">{{ $product->parent_sku }}</p>
        @if ($product->description)
            <div class="mt-4 prose prose-sm max-w-none text-gray-700">{!! nl2br(e($product->description)) !!}</div>
        @endif
        <h2 class="mt-8 font-semibold">Variants</h2>
        <ul class="mt-3 space-y-3">
            @foreach ($product->variants as $variant)
                <li class="flex items-center justify-between bg-white border rounded-lg px-4 py-3">
                    <div>
                        <span class="font-medium">{{ $variant->item_sku }}</span>
                        @if ($variant->color || $variant->size)
                            <span class="text-sm text-gray-500 ml-2">
                                {{ collect([$variant->color, $variant->size])->filter()->join(' / ') }}
                            </span>
                        @endif
                        <div class="text-sm">${{ number_format($variant->msrp ?? $product->min_msrp ?? 0, 2) }}</div>
                    </div>
                    <form action="{{ route('store.cart.add') }}" method="post" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="variant_id" value="{{ $variant->id }}">
                        <input type="number" name="quantity" value="1" min="1" max="99" class="w-16 border rounded px-2 py-1 text-sm">
                        <button type="submit" class="bg-blue-600 text-white text-sm px-3 py-1.5 rounded hover:bg-blue-700">Add to cart</button>
                    </form>
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
