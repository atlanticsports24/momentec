@extends('layouts.store')

@section('title', 'Order confirmed')

@section('content')
<div class="max-w-xl mx-auto text-center">
    <h1 class="text-2xl font-bold text-green-700">Thank you for your order</h1>
    <p class="mt-4 text-gray-600">Order number: <strong>{{ $order->order_number }}</strong></p>
    <p class="mt-2">Status: <strong>{{ $order->status->name }}</strong></p>
    <p class="mt-2 text-lg font-semibold">Total: ${{ number_format($order->total, 2) }}</p>
    <a href="{{ route('store.shop') }}" class="inline-block mt-8 text-blue-600 underline">Continue shopping</a>
</div>
@endsection
