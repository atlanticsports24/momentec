@extends('layouts.app')

@section('title', 'Order Confirmed')

@section('content')
<div style="max-width:600px;margin:60px auto;padding:0 24px;text-align:center;">
    <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:24px;padding:48px 40px;">
        <div style="width:72px;height:72px;background:#d1fae5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
            <svg width="36" height="36" fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1 style="font-size:1.8rem;font-weight:900;color:#111827;margin:0 0 8px;">Order Confirmed!</h1>
        <p style="font-size:15px;color:#6b7280;margin:0 0 28px;">Thank you for your order. We'll send you a confirmation shortly.</p>
        <div style="background:#f8fafc;border-radius:16px;padding:20px 24px;margin-bottom:28px;text-align:left;">
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e5e7eb;">
                <span style="font-size:13px;color:#9ca3af;font-weight:500;">Order Number</span>
                <span style="font-size:14px;font-weight:800;color:#4f46e5;">#{{ $order->order_number }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e5e7eb;">
                <span style="font-size:13px;color:#9ca3af;font-weight:500;">Status</span>
                <span style="font-size:14px;font-weight:700;color:#059669;">{{ $order->status->name }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 0;">
                <span style="font-size:13px;color:#9ca3af;font-weight:500;">Total</span>
                <span style="font-size:16px;font-weight:900;color:#111827;">${{ number_format($order->total, 2) }}</span>
            </div>
        </div>
        <a href="{{ route('products.index') }}"
           style="display:inline-flex;align-items:center;gap:8px;background:#4f46e5;color:#fff;padding:14px 32px;border-radius:14px;font-size:15px;font-weight:700;text-decoration:none;transition:background .2s;"
           onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
            Continue Shopping
        </a>
    </div>
</div>
@endsection
