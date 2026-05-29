<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $customer = Auth::guard('customer')->user();
        $customer->load('addresses');

        $recentOrders = $customer->orders()->with(['status', 'products'])->take(5)->get();
        $wishlistCount = $customer->wishlist()->count();
        $orderCount = $customer->orders()->count();

        return view('customer.dashboard', compact('customer', 'recentOrders', 'wishlistCount', 'orderCount'));
    }

    public function orders(): View
    {
        $orders = Auth::guard('customer')->user()
            ->orders()
            ->with(['status', 'products'])
            ->paginate(10);

        return view('customer.orders', compact('orders'));
    }

    public function orderDetail(Order $order): View
    {
        $customer = Auth::guard('customer')->user();
        abort_if($order->customer_id !== $customer->id, 403);

        $order->load([
            'status',
            'products.variant',
            'totals',
            'paymentMethod',
            'shippingMethod',
            'shippingCountry',
            'shippingZone',
        ]);

        return view('customer.order-detail', compact('order'));
    }

    public function wishlist(): View
    {
        $items = Auth::guard('customer')->user()
            ->wishlist()
            ->with(['product.brand', 'product.images', 'product.variants'])
            ->paginate(12);

        return view('customer.wishlist', compact('items'));
    }

    public function wishlistToggle(Product $product): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $exists = Wishlist::query()
            ->where('customer_id', $customer->id)
            ->where('product_id', $product->id)
            ->first();

        if ($exists) {
            $exists->delete();
            $msg = 'Removed from wishlist.';
        } else {
            Wishlist::query()->create([
                'customer_id' => $customer->id,
                'product_id' => $product->id,
            ]);
            $msg = 'Added to wishlist!';
        }

        return back()->with('success', $msg);
    }

    public function account(): View
    {
        return view('customer.account', [
            'customer' => Auth::guard('customer')->user(),
        ]);
    }

    public function updateAccount(Request $request): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $data = $request->validate([
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'email' => 'required|email|unique:customers,email,'.$customer->id,
            'telephone' => 'nullable|string|max:20',
        ]);

        $customer->update($data);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $customer->update(['password' => $request->password]);
        }

        return back()->with('success', 'Account updated successfully.');
    }

    public function addresses(): View
    {
        $customer = Auth::guard('customer')->user();
        $addresses = $customer->addresses()->with(['country', 'zone'])->get();
        $countries = Country::query()->where('is_enabled', true)->orderBy('name')->get();

        return view('customer.addresses', compact('addresses', 'countries'));
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $data = $request->validate([
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'company' => 'nullable|string|max:255',
            'address_1' => 'required|string|max:255',
            'address_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'postcode' => 'required|string|max:32',
            'country_id' => 'required|exists:countries,id',
            'zone_id' => 'nullable|exists:zones,id',
            'is_default' => 'nullable|boolean',
        ]);

        if ($request->boolean('is_default') || $customer->addresses()->count() === 0) {
            $customer->addresses()->update(['is_default' => false]);
            $data['is_default'] = true;
        } else {
            $data['is_default'] = false;
        }

        $customer->addresses()->create($data);

        return back()->with('success', 'Address saved successfully.');
    }

    public function destroyAddress(CustomerAddress $address): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();
        abort_if($address->customer_id !== $customer->id, 403);

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $next = $customer->addresses()->first();
            $next?->update(['is_default' => true]);
        }

        return back()->with('success', 'Address removed.');
    }
}
