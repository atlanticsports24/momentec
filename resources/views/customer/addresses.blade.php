@extends('customer.layouts.dashboard')

@section('title', 'Addresses')

@section('dashboard_content')
<h1 style="font-size:1.25rem;font-weight:900;color:#111827;margin:0 0 20px;">Address book</h1>

@if($addresses->isNotEmpty())
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;margin-bottom:28px;">
    @foreach($addresses as $address)
    <div style="background:#fff;border:1.5px solid {{ $address->is_default ? '#4f46e5' : '#e5e7eb' }};border-radius:16px;padding:18px;position:relative;">
        @if($address->is_default)
        <span style="position:absolute;top:12px;right:12px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#4f46e5;background:#eef2ff;padding:3px 8px;border-radius:100px;">Default</span>
        @endif
        <div style="font-size:14px;font-weight:800;color:#111827;margin-bottom:8px;">{{ $address->firstname }} {{ $address->lastname }}</div>
        <p style="font-size:13px;color:#374151;line-height:1.6;margin:0 0 14px;">
            @if($address->company){{ $address->company }}<br>@endif
            {{ $address->address_1 }}<br>
            @if($address->address_2){{ $address->address_2 }}<br>@endif
            {{ $address->city }}, {{ $address->postcode }}<br>
            {{ $address->country?->name }}@if($address->zone), {{ $address->zone->name }}@endif
        </p>
        @if(!$address->is_default)
        <form action="{{ route('customer.addresses.destroy', $address) }}" method="POST" onsubmit="return confirm('Remove this address?');">
            @csrf
            @method('DELETE')
            <button type="submit" style="font-size:12px;font-weight:600;color:#ef4444;background:none;border:none;cursor:pointer;padding:0;">Remove</button>
        </form>
        @endif
    </div>
    @endforeach
</div>
@endif

<div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:18px;padding:24px;max-width:640px;">
    <h2 style="font-size:14px;font-weight:800;color:#111827;margin:0 0 16px;">Add new address</h2>
    <form action="{{ route('customer.addresses.store') }}" method="POST" id="address-form">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
            <div>
                <label class="co-label" style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:5px;">First name</label>
                <input type="text" name="firstname" value="{{ old('firstname', auth('customer')->user()->firstname) }}" required style="width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 13px;font-size:14px;box-sizing:border-box;">
            </div>
            <div>
                <label class="co-label" style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:5px;">Last name</label>
                <input type="text" name="lastname" value="{{ old('lastname', auth('customer')->user()->lastname) }}" required style="width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 13px;font-size:14px;box-sizing:border-box;">
            </div>
        </div>
        <div style="margin-bottom:12px;">
            <label class="co-label" style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:5px;">Company (optional)</label>
            <input type="text" name="company" value="{{ old('company') }}" style="width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 13px;font-size:14px;box-sizing:border-box;">
        </div>
        <div style="margin-bottom:12px;">
            <label class="co-label" style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:5px;">Address line 1</label>
            <input type="text" name="address_1" value="{{ old('address_1') }}" required style="width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 13px;font-size:14px;box-sizing:border-box;">
        </div>
        <div style="margin-bottom:12px;">
            <label class="co-label" style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:5px;">Address line 2</label>
            <input type="text" name="address_2" value="{{ old('address_2') }}" style="width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 13px;font-size:14px;box-sizing:border-box;">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
            <div>
                <label class="co-label" style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:5px;">City</label>
                <input type="text" name="city" value="{{ old('city') }}" required style="width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 13px;font-size:14px;box-sizing:border-box;">
            </div>
            <div>
                <label class="co-label" style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:5px;">Postcode</label>
                <input type="text" name="postcode" value="{{ old('postcode') }}" required style="width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 13px;font-size:14px;box-sizing:border-box;">
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
            <div>
                <label class="co-label" style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:5px;">Country</label>
                <select name="country_id" id="country_id" required style="width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 13px;font-size:14px;box-sizing:border-box;">
                    <option value="">Select country</option>
                    @foreach($countries as $country)
                    <option value="{{ $country->id }}" @selected(old('country_id') == $country->id)>{{ $country->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="co-label" style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:5px;">State / Region</label>
                <select name="zone_id" id="zone_id" style="width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 13px;font-size:14px;box-sizing:border-box;">
                    <option value="">Select region</option>
                </select>
            </div>
        </div>
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#374151;margin-bottom:16px;cursor:pointer;">
            <input type="checkbox" name="is_default" value="1" style="accent-color:#4f46e5;" @checked(old('is_default'))>
            Set as default address
        </label>
        <button type="submit" style="background:#4f46e5;color:#fff;border:none;border-radius:12px;padding:12px 24px;font-size:14px;font-weight:800;cursor:pointer;">Save address</button>
    </form>
</div>

@push('scripts')
<script>
(function () {
    const countrySelect = document.getElementById('country_id');
    const zoneSelect = document.getElementById('zone_id');
    const zonesUrl = @json(route('store.checkout.zones'));
    const oldZoneId = @json(old('zone_id'));

    async function loadZones(countryId, selectedId) {
        zoneSelect.innerHTML = '<option value="">Select region</option>';
        if (!countryId) return;
        const res = await fetch(zonesUrl + '?country_id=' + countryId);
        const zones = await res.json();
        zones.forEach(z => {
            const opt = document.createElement('option');
            opt.value = z.id;
            opt.textContent = z.name;
            if (selectedId && String(z.id) === String(selectedId)) opt.selected = true;
            zoneSelect.appendChild(opt);
        });
    }

    countrySelect?.addEventListener('change', () => loadZones(countrySelect.value));
    if (countrySelect?.value) loadZones(countrySelect.value, oldZoneId);
})();
</script>
@endpush
@endsection
