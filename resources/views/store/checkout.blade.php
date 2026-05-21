@extends('layouts.store')

@section('title', 'Checkout')

@section('content')
<h1 class="text-2xl font-bold mb-6">Checkout</h1>
<div class="grid lg:grid-cols-3 gap-8">
    <form action="{{ route('store.checkout.store') }}" method="post" class="lg:col-span-2 space-y-6" id="checkout-form">
        @csrf
        <section class="bg-white border rounded-lg p-6 space-y-4">
            <h2 class="font-semibold">Contact</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <input type="text" name="customer_firstname" value="{{ old('customer_firstname') }}" placeholder="First name" required class="border rounded-lg px-3 py-2 w-full">
                <input type="text" name="customer_lastname" value="{{ old('customer_lastname') }}" placeholder="Last name" required class="border rounded-lg px-3 py-2 w-full">
            </div>
            <input type="email" name="customer_email" value="{{ old('customer_email') }}" placeholder="Email" required class="border rounded-lg px-3 py-2 w-full">
            <input type="text" name="customer_telephone" value="{{ old('customer_telephone') }}" placeholder="Phone" class="border rounded-lg px-3 py-2 w-full">
        </section>
        <section class="bg-white border rounded-lg p-6 space-y-4">
            <h2 class="font-semibold">Billing address</h2>
            <input type="text" name="payment_address_1" value="{{ old('payment_address_1') }}" placeholder="Address" required class="border rounded-lg px-3 py-2 w-full">
            <div class="grid sm:grid-cols-2 gap-4">
                <input type="text" name="payment_city" value="{{ old('payment_city') }}" placeholder="City" required class="border rounded-lg px-3 py-2 w-full">
                <input type="text" name="payment_postcode" value="{{ old('payment_postcode') }}" placeholder="Postcode" required class="border rounded-lg px-3 py-2 w-full">
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <select name="payment_country_id" id="country" required class="border rounded-lg px-3 py-2 w-full">
                    @foreach ($countries as $country)
                        <option value="{{ $country->id }}" @selected(old('payment_country_id', $defaultCountryId) == $country->id)>{{ $country->name }}</option>
                    @endforeach
                </select>
                <select name="payment_zone_id" id="zone" required class="border rounded-lg px-3 py-2 w-full">
                    @foreach ($zones as $zone)
                        <option value="{{ $zone->id }}" @selected(old('payment_zone_id', $defaultZoneId) == $zone->id)>{{ $zone->name }}</option>
                    @endforeach
                </select>
            </div>
        </section>
        <section class="bg-white border rounded-lg p-6 space-y-4">
            <h2 class="font-semibold">Payment method</h2>
            <div id="payment-methods" class="space-y-2">
                @forelse ($paymentMethods as $method)
                    <label class="flex items-center gap-2 border rounded-lg px-4 py-3 cursor-pointer">
                        <input type="radio" name="payment_method_id" value="{{ $method->id }}" @checked(old('payment_method_id') == $method->id) required>
                        <span>{{ $method->name }}</span>
                    </label>
                @empty
                    <p class="text-sm text-red-600">No payment methods available for this address.</p>
                @endforelse
            </div>
        </section>
        <section class="bg-white border rounded-lg p-6 space-y-4">
            <h2 class="font-semibold">Shipping method</h2>
            <div id="shipping-methods" class="space-y-2">
                @forelse ($shippingMethods as $method)
                    <label class="flex items-center justify-between gap-2 border rounded-lg px-4 py-3 cursor-pointer">
                        <span class="flex items-center gap-2">
                            <input type="radio" name="shipping_method_id" value="{{ $method->id }}" @checked(old('shipping_method_id') == $method->id) required>
                            {{ $method->name }}
                        </span>
                        <span class="text-sm text-gray-600">${{ number_format($method->calculateCost($subtotal), 2) }}</span>
                    </label>
                @empty
                    <p class="text-sm text-red-600">No shipping methods available for this address.</p>
                @endforelse
            </div>
        </section>
        <textarea name="comment" rows="3" placeholder="Order comment (optional)" class="border rounded-lg px-3 py-2 w-full">{{ old('comment') }}</textarea>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700">Place order</button>
    </form>
    <aside class="bg-white border rounded-lg p-6 h-fit">
        <h2 class="font-semibold mb-4">Order summary</h2>
        <ul class="text-sm space-y-2 mb-4">
            @foreach ($lines as $line)
                <li class="flex justify-between">
                    <span>{{ $line['variant']->item_sku }} × {{ $line['quantity'] }}</span>
                    <span>${{ number_format($line['total'], 2) }}</span>
                </li>
            @endforeach
        </ul>
        <p class="font-semibold">Subtotal: ${{ number_format($subtotal, 2) }}</p>
    </aside>
</div>
<script>
document.getElementById('country').addEventListener('change', async function () {
    const countryId = this.value;
    const zoneSelect = document.getElementById('zone');
    const res = await fetch('{{ route('store.checkout.zones') }}?country_id=' + countryId);
    const zones = await res.json();
    zoneSelect.innerHTML = zones.map(z => `<option value="${z.id}">${z.name}</option>`).join('');
    zoneSelect.dispatchEvent(new Event('change'));
});
document.getElementById('zone').addEventListener('change', async function () {
    const countryId = document.getElementById('country').value;
    const zoneId = this.value;
    const res = await fetch(`{{ route('store.checkout.methods') }}?country_id=${countryId}&zone_id=${zoneId}`);
    const data = await res.json();
    const pay = document.getElementById('payment-methods');
    pay.innerHTML = data.payment.length
        ? data.payment.map(m => `<label class="flex items-center gap-2 border rounded-lg px-4 py-3"><input type="radio" name="payment_method_id" value="${m.id}" required> ${m.name}</label>`).join('')
        : '<p class="text-sm text-red-600">No payment methods available.</p>';
    const ship = document.getElementById('shipping-methods');
    ship.innerHTML = data.shipping.length
        ? data.shipping.map(m => `<label class="flex justify-between border rounded-lg px-4 py-3"><span class="flex gap-2"><input type="radio" name="shipping_method_id" value="${m.id}" required> ${m.name}</span><span>$${Number(m.cost).toFixed(2)}</span></label>`).join('')
        : '<p class="text-sm text-red-600">No shipping methods available.</p>';
});
</script>
@endsection
