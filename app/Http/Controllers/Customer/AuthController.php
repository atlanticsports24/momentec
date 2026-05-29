<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(Request $request): View
    {
        return view('customer.auth.register', [
            'redirect' => $request->query('redirect'),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|min:8|confirmed',
            'telephone' => 'nullable|string|max:20',
        ]);

        $customer = Customer::query()->create($data);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        $redirect = $request->input('redirect') ?? $request->query('redirect');
        if ($redirect === 'checkout') {
            return redirect()->route('store.checkout')
                ->with('success', 'Account created! Complete your order below.');
        }

        return redirect()->route('customer.dashboard')
            ->with('success', 'Welcome, '.$customer->firstname.'!');
    }

    public function showLogin(Request $request): View
    {
        return view('customer.auth.login', [
            'redirect' => $request->query('redirect'),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $redirect = $request->input('redirect') ?? $request->query('redirect');
            if ($redirect === 'checkout') {
                return redirect()->route('store.checkout')
                    ->with('success', 'Welcome back! Complete your order below.');
            }

            return redirect()->intended(route('customer.dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
    }

    public function registerAjax(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer = Customer::query()->create($validator->validated());
        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully!',
            'customer' => [
                'name' => $customer->full_name,
                'email' => $customer->email,
            ],
        ]);
    }

    public function loginAjax(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'remember' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $validator->only(['email', 'password']);

        if (Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $customer = Auth::guard('customer')->user();

            return response()->json([
                'success' => true,
                'message' => 'Welcome back!',
                'customer' => [
                    'name' => $customer->full_name,
                    'email' => $customer->email,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid email or password.',
        ], 422);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Logged out successfully.');
    }
}
