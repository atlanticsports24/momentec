@extends('customer.layouts.dashboard')

@section('title', 'Order #'.$order->order_number)

@section('dashboard_content')
<div style="margin-bottom:20px;">
    <a href="{{ route('customer.orders') }}" style="font-size:13px;font-weight:600;color:#4f46e5;text-decoration:none;">← Back to orders</a>
</div>

<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:20px;">
    <div>
        <h1 style="font-size:1.25rem;font-weight:900;color:#111827;margin:0;">Order #{{ $order->order_number }}</h1>
        <p style="font-size:13px;color:#6b7280;margin:4px 0 0;">Placed {{ $order->created_at->format('F j, Y \a\t g:i A') }}</p>
    </div>
    <span style="font-size:12px;font-weight:700;padding:6px 14px;border-radius:100px;background:{{ $order->status?->color ?? '#6b7280' }}20;color:{{ $order->status?->color ?? '#6b7280' }};">
        {{ $order->status?->name ?? 'Pending' }}
    </span>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">
    <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:18px;overflow:hidden;">
        <div style="padding:14px 20px;border-bottom:1px solid #f1f5f9;font-size:14px;font-weight:800;color:#111827;">Items</div>
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f9fafb;text-align:left;">
                    <th style="padding:10px 16px;font-weight:700;color:#6b7280;">Product</th>
                    <th style="padding:10px 8px;font-weight:700;color:#6b7280;">Qty</th>
                    <th style="padding:10px 16px;font-weight:700;color:#6b7280;text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->products as $line)
                <tr style="border-top:1px solid #f9fafb;">
                    <td style="padding:12px 16px;">
                        <div style="font-weight:700;color:#111827;">{{ $line->name }}</div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:2px;">SKU: {{ $line->item_sku }}
                            @if(!empty($line->options['color']) || !empty($line->options['size']))
                                · {{ collect($line->options)->filter()->implode(' / ') }}
                            @endif
                        </div>
                    </td>
                    <td style="padding:12px 8px;color:#374151;">{{ $line->quantity }}</td>
                    <td style="padding:12px 16px;text-align:right;font-weight:800;">${{ number_format($line->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="display:flex;flex-direction:column;gap:14px;">
        <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:18px;padding:18px 20px;">
            <div style="font-size:13px;font-weight:800;color:#111827;margin-bottom:12px;">Order summary</div>
            @foreach($order->totals as $total)
            <div style="display:flex;justify-content:space-between;font-size:13px;padding:6px 0;{{ $total->code === 'total' ? 'font-weight:900;border-top:2px solid #e5e7eb;margin-top:8px;padding-top:12px;font-size:15px;' : '' }}">
                <span style="color:{{ $total->code === 'total' ? '#111827' : '#6b7280' }};">{{ $total->title }}</span>
                <span>${{ number_format($total->value, 2) }}</span>
            </div>
            @endforeach
        </div>

        <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:18px;padding:18px 20px;">
            <div style="font-size:13px;font-weight:800;color:#111827;margin-bottom:10px;">Shipping address</div>
            <p style="font-size:13px;color:#374151;line-height:1.6;margin:0;">
                {{ $order->shipping_firstname }} {{ $order->shipping_lastname }}<br>
                {{ $order->shipping_address_1 }}<br>
                @if($order->shipping_address_2){{ $order->shipping_address_2 }}<br>@endif
                {{ $order->shipping_city }}, {{ $order->shipping_postcode }}<br>
                {{ $order->shippingCountry?->name }}@if($order->shippingZone), {{ $order->shippingZone->name }}@endif
            </p>
        </div>

        @if($order->payment_method_name || $order->shipping_method_name)
        <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:18px;padding:18px 20px;font-size:13px;color:#374151;">
            @if($order->payment_method_name)
            <div style="margin-bottom:8px;"><strong style="color:#111827;">Payment:</strong> {{ $order->payment_method_name }}</div>
            @endif
            @if($order->shipping_method_name)
            <div><strong style="color:#111827;">Shipping:</strong> {{ $order->shipping_method_name }}</div>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
