<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Order;
use App\Models\Zone;
use App\Services\Store\CartService;
use App\Services\Store\GeoZoneResolver;
use App\Services\Store\OrderService;
use App\Services\Store\StoreSettings;
use App\Services\Store\TaxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly GeoZoneResolver $geoZones,
        private readonly OrderService $orders,
        private readonly StoreSettings $settings,
        private readonly TaxService $tax,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        if ($this->cart->count() === 0) {
            return redirect()->route('store.cart')->with('error', 'Your cart is empty.');
        }

        $subtotal = $this->cart->subtotal();
        $countryId = (int) ($request->old('payment_country_id') ?: $this->settings->get('default_country_id'));
        $zoneId = (int) ($request->old('payment_zone_id') ?: $this->settings->get('default_zone_id'));
        $tax = $this->tax->calculate($subtotal, $zoneId ?: null);

        return view('store.checkout', [
            'lines' => $this->cart->lines(),
            'subtotal' => $subtotal,
            'countries' => Country::query()->where('is_enabled', true)->orderBy('name')->get(),
            'zones' => Zone::query()->where('country_id', $countryId)->where('is_enabled', true)->orderBy('name')->get(),
            'paymentMethods' => $this->geoZones->availablePaymentMethods($countryId, $zoneId ?: null, $subtotal),
            'shippingMethods' => $this->geoZones->availableShippingMethods($countryId, $zoneId ?: null, $subtotal),
            'defaultCountryId' => $countryId,
            'defaultZoneId' => $zoneId,
            'tax' => $tax,
        ]);
    }

    public function zones(Request $request)
    {
        $countryId = (int) $request->query('country_id');

        return response()->json(
            Zone::query()
                ->where('country_id', $countryId)
                ->where('is_enabled', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'tax_rate'])
        );
    }

    public function methods(Request $request)
    {
        $validated = $request->validate([
            'country_id' => ['required', 'exists:countries,id'],
            'zone_id' => ['nullable', 'exists:zones,id'],
        ]);

        $subtotal = $this->cart->subtotal();
        $countryId = (int) $validated['country_id'];
        $zoneId = isset($validated['zone_id']) ? (int) $validated['zone_id'] : null;

        return response()->json([
            'payment' => $this->geoZones->availablePaymentMethods($countryId, $zoneId, $subtotal)->values(),
            'shipping' => $this->geoZones->availableShippingMethods($countryId, $zoneId, $subtotal)->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'code' => $m->code,
                'cost' => $m->calculateCost($subtotal),
            ])->values(),
            'tax' => $this->tax->toArray($subtotal, $zoneId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($this->cart->count() === 0) {
            return redirect()->route('store.cart');
        }

        $validated = $request->validate([
            'customer_email' => ['required', 'email'],
            'customer_firstname' => ['required', 'string', 'max:255'],
            'customer_lastname' => ['required', 'string', 'max:255'],
            'customer_telephone' => ['nullable', 'string', 'max:255'],
            'payment_address_1' => ['required', 'string', 'max:255'],
            'payment_city' => ['required', 'string', 'max:255'],
            'payment_postcode' => ['required', 'string', 'max:32'],
            'payment_country_id' => ['required', 'exists:countries,id'],
            'payment_zone_id' => ['required', 'exists:zones,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'shipping_method_id' => ['required', 'exists:shipping_methods,id'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $subtotal = $this->cart->subtotal();
        $countryId = (int) $validated['payment_country_id'];
        $zoneId = (int) $validated['payment_zone_id'];

        $payments = $this->geoZones->availablePaymentMethods($countryId, $zoneId, $subtotal);
        $shipping = $this->geoZones->availableShippingMethods($countryId, $zoneId, $subtotal);

        $paymentId = (int) $validated['payment_method_id'];
        $shippingId = (int) $validated['shipping_method_id'];

        if (! $payments->contains(fn ($m) => $m->id === $paymentId)) {
            return back()->withErrors(['payment_method_id' => 'Invalid payment method for this address.'])->withInput();
        }

        if (! $shipping->contains(fn ($m) => $m->id === $shippingId)) {
            return back()->withErrors(['shipping_method_id' => 'Invalid shipping method for this address.'])->withInput();
        }

        $order = $this->orders->createFromCheckout($validated);

        if (Auth::guard('customer')->check()) {
            $order->update(['customer_id' => Auth::guard('customer')->id()]);
        }

        return redirect()->route('store.checkout.success', $order);
    }

    public function success(Order $order): View
    {
        return view('store.checkout-success', ['order' => $order->load(['status', 'products', 'totals'])]);
    }
}
