@php
    $hasSelectable = $shippingQuotes->contains(fn ($q) => empty($q['error']));
@endphp
@forelse($shippingQuotes as $q)
    @if(!empty($q['error']))
        <p style="font-size:13px;color:#ef4444;margin-bottom:8px;">{{ $q['name'] ?? 'Shipping' }}: {{ $q['error'] }}</p>
        @continue
    @endif
    <label class="co-option" @click="selectShipping({{ $q['cost'] }})">
        <div style="display:flex;align-items:center;">
            <input type="radio" name="shipping_option" value="{{ $q['key'] }}"
                data-cost="{{ $q['cost'] }}"
                @checked($selectedShippingKey === $q['key'])
                @change="selectShipping({{ $q['cost'] }})"
                required>
            <span class="co-option-label">{{ $q['name'] }}</span>
        </div>
        <span class="co-option-price">${{ number_format($q['cost'], 2) }}</span>
    </label>
@empty
    <p style="font-size:13px;color:#ef4444;">No shipping methods available.</p>
@endforelse
@if(!$hasSelectable && $shippingQuotes->isEmpty())
    <p style="font-size:13px;color:#ef4444;">No shipping methods available.</p>
@endif
