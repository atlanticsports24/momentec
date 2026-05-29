@extends('customer.layouts.dashboard')

@section('title', 'My Orders')

@section('dashboard_content')
<h1 style="font-size:1.25rem;font-weight:900;color:#111827;margin:0 0 20px;">My Orders</h1>

<div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:18px;overflow:hidden;">
    @forelse($orders as $order)
    <div style="padding:16px 20px;border-bottom:1px solid #f9fafb;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div>
            <div style="font-size:14px;font-weight:800;color:#111827;">#{{ $order->order_number }}</div>
            <div style="font-size:12px;color:#9ca3af;margin-top:2px;">{{ $order->created_at->format('M d, Y · g:i A') }} · {{ $order->products->count() }} item(s)</div>
        </div>
        <span style="font-size:11px;font-weight:700;padding:4px 12px;border-radius:100px;background:{{ $order->status?->color ?? '#6b7280' }}20;color:{{ $order->status?->color ?? '#6b7280' }};">
            {{ $order->status?->name ?? 'Pending' }}
        </span>
        <div style="font-size:15px;font-weight:900;color:#111827;">${{ number_format($order->total, 2) }}</div>
        <a href="{{ route('customer.order.detail', $order) }}" style="font-size:12px;font-weight:700;color:#4f46e5;text-decoration:none;padding:8px 14px;border:1.5px solid #e5e7eb;border-radius:10px;">View details</a>
    </div>
    @empty
    <div style="padding:48px;text-align:center;color:#9ca3af;font-size:14px;">
        You have no orders yet.<br>
        <a href="{{ route('products.index') }}" style="color:#4f46e5;font-weight:600;margin-top:8px;display:inline-block;">Browse products →</a>
    </div>
    @endforelse
</div>

@if($orders->hasPages())
<div style="margin-top:20px;">{{ $orders->links('components.pagination') }}</div>
@endif
@endsection
