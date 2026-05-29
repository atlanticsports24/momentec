@extends('customer.layouts.dashboard')

@section('title', 'My Dashboard')

@section('dashboard_content')
<div style="background:linear-gradient(135deg,#1a1a2e,#2d2b55);border-radius:18px;padding:24px 28px;margin-bottom:20px;color:#fff;">
    <div style="font-size:11px;color:rgba(255,255,255,.5);font-weight:600;text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;">Welcome back</div>
    <div style="font-size:1.4rem;font-weight:900;">{{ $customer->full_name }}</div>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px;">
    @foreach([['Orders', $orderCount, '#eef2ff', '#4f46e5'], ['Wishlist', $wishlistCount, '#d1fae5', '#059669'], ['Addresses', $customer->addresses->count(), '#fef3c7', '#d97706']] as [$label, $val, $bg, $color])
    <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:16px;padding:20px;text-align:center;">
        <div style="font-size:2rem;font-weight:900;color:{{ $color }};">{{ $val }}</div>
        <div style="font-size:12px;color:#6b7280;font-weight:600;margin-top:4px;">{{ $label }}</div>
    </div>
    @endforeach
</div>

<div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:18px;overflow:hidden;">
    <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
        <span style="font-size:14px;font-weight:800;color:#111827;">Recent Orders</span>
        <a href="{{ route('customer.orders') }}" style="font-size:12px;font-weight:600;color:#4f46e5;text-decoration:none;">View all →</a>
    </div>
    @forelse($recentOrders as $order)
    <div style="padding:14px 20px;border-bottom:1px solid #f9fafb;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div>
            <div style="font-size:13px;font-weight:700;color:#111827;">#{{ $order->order_number }}</div>
            <div style="font-size:11px;color:#9ca3af;">{{ $order->created_at->format('M d, Y') }}</div>
        </div>
        <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:100px;background:{{ $order->status?->color ?? '#6b7280' }}20;color:{{ $order->status?->color ?? '#6b7280' }};">
            {{ $order->status?->name ?? 'Pending' }}
        </span>
        <div style="font-size:14px;font-weight:800;color:#111827;">${{ number_format($order->total, 2) }}</div>
        <a href="{{ route('customer.order.detail', $order) }}" style="font-size:12px;font-weight:600;color:#4f46e5;text-decoration:none;">View →</a>
    </div>
    @empty
    <div style="padding:32px;text-align:center;color:#9ca3af;font-size:13px;">No orders yet. <a href="{{ route('products.index') }}" style="color:#4f46e5;">Start shopping →</a></div>
    @endforelse
</div>
@endsection
