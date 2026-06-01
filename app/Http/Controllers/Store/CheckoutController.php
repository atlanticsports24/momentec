<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Zone;
use App\Services\Store\AuthorizeNetAimService;
use App\Services\Store\CartService;
use App\Services\Store\GeoZoneResolver;
use App\Services\Store\OrderService;
use App\Services\Store\ShippingQuoteService;
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
        private readonly ShippingQuoteService $shippingQuotes,
        private readonly OrderService $orders,
        private readonly StoreSettings $settings,
        private readonly TaxService $tax,
        private readonly AuthorizeNetAimService $authorizeNet,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        if ($this->cart->count() === 0) {
            return redirect()->route('store.cart')->with('error', 'Your cart is empty.');
        }

        $subtotal = $this->cart->subtotal();
        $authCustomer = Auth::guard('customer')->user();
        $defaultAddress = $authCustomer?->defaultAddress;

        $countryId = (int) ($request->old('payment_country_id') ?: $defaultAddress?->country_id ?: $this->settings->get('default_country_id'));
        $zoneId = (int) ($request->old('payment_zone_id') ?: $defaultAddress?->zone_id ?: $this->settings->get('default_zone_id'));
        $city = (string) $request->old('payment_city', $defaultAddress?->city ?? '');
        $postcode = (string) $request->old('payment_postcode', $defaultAddress?->postcode ?? '');
        $tax = $this->tax->calculate($subtotal, $zoneId ?: null);

        return view('store.checkout', [
            'lines' => $this->cart->lines(),
            'subtotal' => $subtotal,
            'countries' => Country::query()->where('is_enabled', true)->orderBy('name')->get(),
            'zones' => Zone::query()->where('country_id', $countryId)->where('is_enabled', true)->orderBy('name')->get(),
            'paymentMethods' => $this->geoZones->availablePaymentMethods($countryId, $zoneId ?: null, $subtotal),
            'shippingQuotes' => $this->shippingQuotesForDisplay($countryId, $zoneId, $city, $postcode),
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
            'city' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:32'],
        ]);

        $subtotal = $this->cart->subtotal();
        $countryId = (int) $validated['country_id'];
        $zoneId = isset($validated['zone_id']) ? (int) $validated['zone_id'] : null;

        return response()->json([
            'payment' => $this->geoZones->availablePaymentMethods($countryId, $zoneId, $subtotal)->map(fn (PaymentMethod $m) => [
                'id' => $m->id,
                'name' => $m->name,
                'code' => $m->code,
            ])->values(),
            'shipping' => $this->shippingQuotesForDisplay(
                $countryId,
                $zoneId,
                (string) ($validated['city'] ?? ''),
                (string) ($validated['postcode'] ?? ''),
            )->values(),
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
            'shipping_option' => ['required', 'string', 'max:64'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $subtotal = $this->cart->subtotal();
        $countryId = (int) $validated['payment_country_id'];
        $zoneId = (int) $validated['payment_zone_id'];

        $payments = $this->geoZones->availablePaymentMethods($countryId, $zoneId, $subtotal);

        $paymentId = (int) $validated['payment_method_id'];

        if (! $payments->contains(fn ($m) => $m->id === $paymentId)) {
            return back()->withErrors(['payment_method_id' => 'Invalid payment method for this address.'])->withInput();
        }

        [$shippingMethodId, $serviceCode] = $this->parseShippingOption($validated['shipping_option']);

        $shippingSelection = $this->shippingQuotes->resolveSelection(
            $countryId,
            $zoneId,
            $validated['payment_city'],
            $validated['payment_postcode'],
            $shippingMethodId,
            $serviceCode,
        );

        if (! $shippingSelection) {
            return back()->withErrors(['shipping_option' => 'Invalid or expired shipping option. Please select shipping again.'])->withInput();
        }

        $validated['shipping_method_id'] = $shippingSelection['method']->id;
        $validated['shipping_cost'] = $shippingSelection['cost'];
        $validated['shipping_method_name'] = $shippingSelection['name'];
        $validated['shipping_method_code'] = $shippingSelection['service_code']
            ? $shippingSelection['method']->code.'.'.$shippingSelection['service_code']
            : $shippingSelection['method']->code;

        $paymentMethod = PaymentMethod::query()->findOrFail($paymentId);

        if ($paymentMethod->code === 'authorize_net') {
            $request->validate([
                'cc_number' => ['required', 'string', 'regex:/^[0-9\s]{13,19}$/'],
                'cc_expire_date_month' => ['required', 'regex:/^(0[1-9]|1[0-2])$/'],
                'cc_expire_date_year' => ['required', 'regex:/^\d{2,4}$/'],
                'cc_cvv2' => ['required', 'regex:/^[0-9]{3,4}$/'],
            ]);
        }

        $isAuthorizeNet = $paymentMethod->code === 'authorize_net';
        $order = $this->orders->createFromCheckout($validated, clearCart: ! $isAuthorizeNet);

        if (Auth::guard('customer')->check()) {
            $order->update(['customer_id' => Auth::guard('customer')->id()]);
        }

        if ($isAuthorizeNet) {
            $result = $this->authorizeNet->charge($order, $paymentMethod, [
                'number' => $request->input('cc_number'),
                'exp_month' => $request->input('cc_expire_date_month'),
                'exp_year' => $request->input('cc_expire_date_year'),
                'cvv' => $request->input('cc_cvv2'),
            ]);

            if (! $result['success']) {
                $this->orders->markPaymentFailed($order, $paymentMethod, $result['error'] ?? 'Payment declined');

                return back()
                    ->withErrors(['payment' => $result['error'] ?? 'Payment was declined.'])
                    ->withInput();
            }

            $this->orders->markPaymentSuccess($order, $paymentMethod, $result['history_comment'] ?? null);
            $this->cart->clear();
        }

        return redirect()->route('store.checkout.success', $order);
    }

    public function success(Order $order): View
    {
        return view('store.checkout-success', ['order' => $order->load(['status', 'products', 'totals'])]);
    }

  /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function shippingQuotesForDisplay(int $countryId, ?int $zoneId, string $city, string $postcode)
    {
        if ($zoneId && $city !== '' && $postcode !== '') {
            return $this->shippingQuotes->quotesForAddress($countryId, $zoneId, $city, $postcode);
        }

        $subtotal = $this->cart->subtotal();

        return $this->geoZones->availableShippingMethods($countryId, $zoneId, $subtotal)
            ->reject(fn ($m) => in_array($m->code, ['ups', 'usps'], true))
            ->map(fn ($m) => [
                'key' => (string) $m->id,
                'method_id' => $m->id,
                'service_code' => null,
                'code' => $m->code,
                'name' => $m->name,
                'cost' => $m->calculateCost($subtotal),
                'error' => null,
            ])
            ->values();
    }

    /**
     * @return array{0: int, 1: ?string}
     */
    private function parseShippingOption(string $option): array
    {
        if (str_contains($option, '|')) {
            [$methodId, $serviceCode] = explode('|', $option, 2);

            return [(int) $methodId, $serviceCode !== '' ? $serviceCode : null];
        }

        return [(int) $option, null];
    }
}
