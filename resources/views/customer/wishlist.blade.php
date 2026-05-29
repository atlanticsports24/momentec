@extends('customer.layouts.dashboard')

@section('title', 'Wishlist')

@section('dashboard_content')
<h1 style="font-size:1.25rem;font-weight:900;color:#111827;margin:0 0 20px;">Wishlist</h1>

@if($items->isEmpty())
<div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:18px;padding:48px;text-align:center;color:#9ca3af;font-size:14px;">
    Your wishlist is empty.<br>
    <a href="{{ route('products.index') }}" style="color:#4f46e5;font-weight:600;margin-top:8px;display:inline-block;">Discover products →</a>
</div>
@else
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;">
    @foreach($items as $item)
    @if($item->product)
    <div style="position:relative;">
        <x-product-card :product="$item->product" />
        <form action="{{ route('customer.wishlist.toggle', $item->product) }}" method="POST" style="position:absolute;top:12px;right:12px;z-index:2;">
            @csrf
            <button type="submit" title="Remove from wishlist" style="width:36px;height:36px;border-radius:50%;background:#fff;border:1.5px solid #e5e7eb;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.08);">
                <svg width="16" height="16" fill="#ef4444" stroke="#ef4444" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </button>
        </form>
    </div>
    @endif
    @endforeach
</div>

@if($items->hasPages())
<div style="margin-top:24px;">{{ $items->links('components.pagination') }}</div>
@endif
@endif
@endsection
