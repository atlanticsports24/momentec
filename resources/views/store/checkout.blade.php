@extends('layouts.app')

@section('title', 'Checkout')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('styles')
<style>
.co-wrap { max-width:1100px; margin:0 auto; padding:28px 24px 60px; }
.co-page-title { font-size:1.4rem; font-weight:900; color:#111827; margin-bottom:24px; display:flex; align-items:center; gap:12px; }
.co-page-title a { font-size:13px; font-weight:600; color:#4f46e5; text-decoration:none; margin-left:auto; }
.co-grid { display:grid; grid-template-columns:1fr 340px; gap:24px; align-items:start; }
@media(max-width:900px) { .co-grid { grid-template-columns:1fr; } }
/* Steps */
.co-steps { display:flex; align-items:center; margin-bottom:24px; gap:0; }
.co-step { display:flex; align-items:center; gap:8px; font-size:12px; font-weight:600; color:#9ca3af; }
.co-step.active { color:#4f46e5; }
.co-step.done { color:#059669; }
.co-step-dot { width:26px; height:26px; border-radius:50%; border:2px solid currentColor; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800; flex-shrink:0; }
.co-step-done .co-step-dot { background:#059669; border-color:#059669; color:#fff; }
.co-step-line { flex:1; height:1.5px; background:#e5e7eb; margin:0 10px; }
/* Section card */
.co-card { background:#fff; border:1.5px solid #e5e7eb; border-radius:16px; padding:20px 24px; margin-bottom:14px; }
.co-card-title { font-size:14px; font-weight:800; color:#111827; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
.co-card-icon { width:28px; height:28px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
/* Inputs */
.co-label { display:block; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; margin-bottom:5px; }
.co-input { width:100%; border:1.5px solid #e5e7eb; border-radius:10px; padding:10px 13px; font-size:14px; color:#111827; outline:none; background:#fff; transition:border-color .2s; box-sizing:border-box; }
.co-input:focus { border-color:#4f46e5; }
.co-field { margin-bottom:12px; }
.co-row-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
@media(max-width:480px) { .co-row-2 { grid-template-columns:1fr; } }
/* Radio options */
.co-option { display:flex; align-items:center; justify-content:space-between; border:1.5px solid #e5e7eb; border-radius:10px; padding:11px 14px; margin-bottom:8px; cursor:pointer; transition:border-color .2s,background .2s; }
.co-option:has(input:checked) { border-color:#4f46e5; background:#fafaff; }
.co-option input[type=radio] { accent-color:#4f46e5; width:15px; height:15px; flex-shrink:0; }
.co-option-label { font-size:13px; font-weight:600; color:#111827; margin-left:8px; }
.co-option-price { font-size:13px; font-weight:700; color:#059669; }
/* Place order btn */
.co-place-btn { width:100%; background:#4f46e5; color:#fff; border:none; border-radius:12px; padding:15px; font-size:15px; font-weight:800; cursor:pointer; transition:all .2s; margin-top:16px; letter-spacing:.02em; }
.co-place-btn:hover { background:#4338ca; box-shadow:0 6px 20px rgba(79,70,229,.35); transform:translateY(-1px); }
/* Summary sidebar */
.co-summary { background:#fff; border:1.5px solid #e5e7eb; border-radius:16px; padding:20px; position:sticky; top:80px; }
.co-summary-title { font-size:14px; font-weight:800; color:#111827; margin-bottom:14px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; }
.co-sum-item { display:flex; align-items:flex-start; gap:10px; padding:10px 0; border-bottom:1px solid #f9fafb; }
.co-sum-img { width:44px; height:44px; border-radius:8px; background:#f8fafc; border:1px solid #e5e7eb; overflow:hidden; flex-shrink:0; }
.co-sum-img img { width:100%; height:100%; object-fit:contain; }
.co-sum-name { font-size:12px; font-weight:600; color:#111827; line-height:1.4; }
.co-sum-meta { font-size:11px; color:#9ca3af; margin-top:1px; }
.co-sum-price { font-size:13px; font-weight:800; color:#111827; margin-left:auto; white-space:nowrap; padding-left:8px; }
.co-sum-row { display:flex; justify-content:space-between; font-size:13px; padding:7px 0; }
.co-sum-total { display:flex; justify-content:space-between; font-size:16px; font-weight:900; padding-top:12px; margin-top:4px; border-top:2px solid #e5e7eb; }
/* Account tabs */
.co-tab-wrap { display:inline-flex; background:#f3f4f6; border-radius:10px; padding:4px; gap:2px; margin-bottom:18px; }
.co-tab-btn { padding:7px 14px; border-radius:7px; border:none; font-size:12px; font-weight:700; cursor:pointer; transition:all .2s; background:transparent; color:#6b7280; }
.co-tab-btn.on { background:#fff; color:#111827; box-shadow:0 1px 4px rgba(0,0,0,.1); }
.co-tab-btn { padding:7px 16px; border-radius:7px; border:none; font-size:12px; font-weight:700; cursor:pointer; transition:all .2s; background:transparent; color:#6b7280; box-shadow:none; }
.co-tab-btn.active { background:#fff; color:#111827; box-shadow:0 1px 4px rgba(0,0,0,.12); }
/* Note toggle */
.co-note-toggle { font-size:12px; color:#4f46e5; cursor:pointer; background:none; border:none; font-weight:600; padding:0; }
/* Account auth buttons */
.co-auth-btn {
    width:100%;
    box-sizing:border-box;
    background:#4f46e5;
    color:#fff;
    border:none;
    border-radius:12px;
    padding:13px 16px;
    font-size:14px;
    font-weight:700;
    font-family:inherit;
    line-height:1.25;
    letter-spacing:.01em;
    cursor:pointer;
    transition:background .2s, opacity .2s, box-shadow .2s;
    margin-bottom:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    min-height:48px;
    -webkit-appearance:none;
    appearance:none;
}
.co-auth-btn:hover:not(:disabled) {
    background:#4338ca;
    box-shadow:0 4px 14px rgba(79,70,229,.35);
}
.co-auth-btn:disabled {
    opacity:.65;
    cursor:not-allowed;
    box-shadow:none;
}
.co-auth-btn__spin {
    width:16px;
    height:16px;
    flex-shrink:0;
    animation:co-auth-spin 1s linear infinite;
}
@keyframes co-auth-spin {
    from { transform:rotate(0deg); }
    to { transform:rotate(360deg); }
}
</style>
@endpush

@section('content')
<div class="co-wrap">
    <!-- Page title -->
    <div class="co-page-title">
        Checkout
        <a href="{{ route('store.cart') }}">← Back to cart</a>
    </div>
    <!-- Steps -->
    <div class="co-steps">
        <div class="co-step co-step-done done">
            <div class="co-step-dot">✓</div>
            <span>Cart</span>
        </div>
        <div class="co-step-line"></div>
        <div class="co-step active">
            <div class="co-step-dot" style="border-color:#4f46e5;background:#4f46e5;color:#fff;">2</div>
            <span>Details</span>
        </div>
        <div class="co-step-line"></div>
        <div class="co-step">
            <div class="co-step-dot">3</div>
            <span>Confirm</span>
        </div>
    </div>
    @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#dc2626;">
        <ul style="margin:0;padding:0 0 0 16px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif
    @php
        $selectedShippingId = old('shipping_method_id', $shippingMethods->first()?->id);
        $selectedShipping = $shippingMethods->firstWhere('id', $selectedShippingId);
        $initialShippingCost = $selectedShipping ? $selectedShipping->calculateCost($subtotal) : 0;
    @endphp
    <div class="co-grid" id="checkout-root" x-data="{
        shippingCost: {{ $initialShippingCost }},
        subtotal: {{ $subtotal }},
        get total() { return this.subtotal + this.shippingCost; },
        selectShipping(cost) { this.shippingCost = parseFloat(cost) || 0; }
    }">
        <!-- LEFT: Form -->
        <div>
            @guest('customer')
            <div class="co-card" x-data="{
                mode: 'guest',
                showPass: false,
                regLoading: false,
                regError: '',
                regSuccess: false,
                regData: { firstname: '', lastname: '', email: '', password: '', password_confirmation: '', agree: false },
                loginLoading: false,
                loginError: '',
                loginSuccess: false,
                loginData: { email: '', password: '', remember: false },
                csrfToken() {
                    return document.querySelector('meta[name=csrf-token]')?.content || document.querySelector('#checkout-form input[name=_token]')?.value || '';
                },
                firstError(data) {
                    if (data.message) return data.message;
                    const errs = data.errors || {};
                    const first = Object.values(errs)[0];
                    return Array.isArray(first) ? first[0] : (first || 'Request failed.');
                },
                async registerInline() {
                    this.regError = '';
                    if (!this.regData.agree) {
                        this.regError = 'Please agree to the Terms & Conditions.';
                        return;
                    }
                    this.regLoading = true;
                    try {
                        const res = await fetch('{{ route('customer.register.ajax') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken(),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(this.regData),
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.regSuccess = true;
                            this.mode = 'guest';
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            this.regError = this.firstError(data);
                        }
                    } catch (e) {
                        this.regError = 'Something went wrong. Please try again.';
                    }
                    this.regLoading = false;
                },
                async loginInline() {
                    this.loginError = '';
                    this.loginLoading = true;
                    try {
                        const res = await fetch('{{ route('customer.login.ajax') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken(),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(this.loginData),
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.loginSuccess = true;
                            this.mode = 'guest';
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            this.loginError = this.firstError(data);
                        }
                    } catch (e) {
                        this.loginError = 'Something went wrong. Please try again.';
                    }
                    this.loginLoading = false;
                },
            }">
                <div class="co-card-title">
                    <div class="co-card-icon" style="background:#f0fdf4;">
                        <svg width="14" height="14" fill="none" stroke="#16a34a" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    Account
                </div>
                <div style="display:inline-flex;background:#f3f4f6;border-radius:10px;padding:4px;gap:3px;margin-bottom:18px;">
                    <button type="button" @click="mode='guest'" :class="mode==='guest' ? 'co-tab-btn active' : 'co-tab-btn'">Guest</button>
                    <button type="button" @click="mode='login'" :class="mode==='login' ? 'co-tab-btn active' : 'co-tab-btn'">Sign In</button>
                    <button type="button" @click="mode='register'" :class="mode==='register' ? 'co-tab-btn active' : 'co-tab-btn'">Register</button>
                </div>
                <div x-show="mode==='guest'">
                    <div style="display:flex;align-items:center;gap:8px;background:#f8fafc;border-radius:8px;padding:10px 14px;font-size:13px;color:#6b7280;">
                        <svg width="14" height="14" fill="none" stroke="#9ca3af" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Checkout as guest — no account required.
                    </div>
                </div>
                <div x-show="mode==='login'" x-cloak>
                    <div x-show="loginSuccess" style="background:#d1fae5;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:13px;font-weight:600;color:#065f46;">
                        Signed in! Continue filling the form below.
                    </div>
                    <div x-show="loginError" x-text="loginError"
                         style="background:#fef2f2;border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#dc2626;font-weight:600;"></div>
                    <div x-show="!loginSuccess">
                        <div class="co-field">
                            <label class="co-label">Email</label>
                            <input type="email" x-model="loginData.email" placeholder="you@email.com" class="co-input">
                        </div>
                        <div class="co-field">
                            <label class="co-label">Password</label>
                            <div style="position:relative;">
                                <input :type="showPass ? 'text' : 'password'" x-model="loginData.password" placeholder="••••••••" class="co-input" style="padding-right:40px;">
                                <button type="button" @click="showPass=!showPass"
                                        style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;display:flex;">
                                    <svg x-show="!showPass" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="showPass" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
                            <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:#374151;cursor:pointer;">
                                <input type="checkbox" x-model="loginData.remember" style="accent-color:#4f46e5;"> Remember me
                            </label>
                            <a href="{{ route('customer.login') }}" style="font-size:12px;color:#4f46e5;text-decoration:none;">Forgot password?</a>
                        </div>
                        <button type="button"
                                class="co-auth-btn"
                                @click="loginInline()"
                                :disabled="loginLoading">
                            <svg x-show="loginLoading" x-cloak class="co-auth-btn__spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span x-text="loginLoading ? 'Signing in...' : 'Sign In & Continue'"></span>
                        </button>
                        <p style="text-align:center;font-size:12px;color:#9ca3af;margin:10px 0 0;">No account? <button type="button" @click="mode='register'" style="background:none;border:none;color:#4f46e5;font-weight:600;cursor:pointer;font-size:12px;">Register →</button></p>
                    </div>
                </div>
                <div x-show="mode==='register'" x-cloak>
                    <div x-show="regSuccess" style="background:#d1fae5;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:13px;font-weight:600;color:#065f46;">
                        Account created! You are now signed in. Continue filling the form below.
                    </div>
                    <div x-show="regError" x-text="regError"
                         style="background:#fef2f2;border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#dc2626;font-weight:600;"></div>
                    <div x-show="!regSuccess">
                        <div class="co-row-2">
                            <div class="co-field">
                                <label class="co-label">First Name</label>
                                <input type="text" x-model="regData.firstname" placeholder="John" class="co-input">
                            </div>
                            <div class="co-field">
                                <label class="co-label">Last Name</label>
                                <input type="text" x-model="regData.lastname" placeholder="Doe" class="co-input">
                            </div>
                        </div>
                        <div class="co-field">
                            <label class="co-label">Email</label>
                            <input type="email" x-model="regData.email" placeholder="you@email.com" class="co-input">
                        </div>
                        <div class="co-field">
                            <label class="co-label">Password</label>
                            <input type="password" x-model="regData.password" placeholder="Min 8 characters" class="co-input">
                        </div>
                        <div class="co-field">
                            <label class="co-label">Confirm Password</label>
                            <input type="password" x-model="regData.password_confirmation" placeholder="Repeat password" class="co-input">
                        </div>
                        <label style="display:flex;align-items:center;gap:7px;font-size:12px;color:#374151;cursor:pointer;margin-bottom:14px;">
                            <input type="checkbox" x-model="regData.agree" style="accent-color:#4f46e5;"> I agree to Terms & Conditions
                        </label>
                        <button type="button"
                                class="co-auth-btn"
                                :disabled="regLoading"
                                @click="registerInline()">
                            <svg x-show="regLoading" x-cloak class="co-auth-btn__spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span x-text="regLoading ? 'Creating account...' : 'Create Account & Continue'"></span>
                        </button>
                        <p style="text-align:center;font-size:12px;color:#9ca3af;margin:10px 0 0;">
                            Have an account? <button type="button" @click="mode='login'" style="background:none;border:none;color:#4f46e5;font-weight:600;cursor:pointer;font-size:12px;">Sign in →</button>
                        </p>
                    </div>
                </div>
            </div>
            @else
            <div class="co-card">
                <div class="co-card-title">
                    <div class="co-card-icon" style="background:#d1fae5;">
                        <svg width="14" height="14" fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    Account
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;background:#f0fdf4;border-radius:10px;padding:12px 16px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:50%;background:#4f46e5;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#fff;">
                            {{ strtoupper(substr(auth('customer')->user()->firstname, 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:#111827;">{{ auth('customer')->user()->full_name }}</div>
                            <div style="font-size:11px;color:#6b7280;">{{ auth('customer')->user()->email }}</div>
                        </div>
                    </div>
                    <span style="font-size:11px;font-weight:700;color:#059669;background:#d1fae5;padding:3px 10px;border-radius:100px;">✓ Signed in</span>
                </div>
            </div>
            @endguest
            <!-- MAIN FORM -->
            <form action="{{ route('store.checkout.store') }}" method="POST" id="checkout-form">
                @csrf
                @php $authCustomer = auth('customer')->user(); @endphp

                <div class="co-card">
                    <div class="co-card-title">
                        <div class="co-card-icon" style="background:#eef2ff;">
                            <svg width="14" height="14" fill="none" stroke="#4f46e5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        Contact & Shipping
                    </div>

                    <div class="co-field">
                        <label class="co-label">Email *</label>
                        <div style="position:relative;">
                            <input type="email"
                                   name="customer_email"
                                   value="{{ old('customer_email', $authCustomer?->email) }}"
                                   placeholder="you@company.com"
                                   required
                                   class="co-input"
                                   @if($authCustomer) readonly style="background:#f8fafc;color:#6b7280;cursor:not-allowed;padding-right:40px;" @endif>
                            @if($authCustomer)
                            <div style="position:absolute;right:12px;top:50%;transform:translateY(-50%);">
                                <svg width="14" height="14" fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="co-row-2">
                        <div class="co-field">
                            <label class="co-label">First Name *</label>
                            <input type="text"
                                   name="customer_firstname"
                                   value="{{ old('customer_firstname', $authCustomer?->firstname) }}"
                                   placeholder="John"
                                   required
                                   class="co-input">
                        </div>
                        <div class="co-field">
                            <label class="co-label">Last Name *</label>
                            <input type="text"
                                   name="customer_lastname"
                                   value="{{ old('customer_lastname', $authCustomer?->lastname) }}"
                                   placeholder="Doe"
                                   required
                                   class="co-input">
                        </div>
                    </div>

                    <div class="co-field">
                        <label class="co-label">Street Address *</label>
                        <input type="text"
                               name="payment_address_1"
                               value="{{ old('payment_address_1', $authCustomer?->defaultAddress?->address_1) }}"
                               placeholder="123 Main Street"
                               required
                               class="co-input">
                    </div>
                    <div class="co-row-2">
                        <div class="co-field">
                            <label class="co-label">City *</label>
                            <input type="text"
                                   name="payment_city"
                                   value="{{ old('payment_city', $authCustomer?->defaultAddress?->city) }}"
                                   placeholder="New York"
                                   required
                                   class="co-input">
                        </div>
                        <div class="co-field">
                            <label class="co-label">Postcode *</label>
                            <input type="text"
                                   name="payment_postcode"
                                   value="{{ old('payment_postcode', $authCustomer?->defaultAddress?->postcode) }}"
                                   placeholder="10001"
                                   required
                                   class="co-input">
                        </div>
                    </div>
                    <div class="co-row-2">
                        <div class="co-field">
                            <label class="co-label">Country *</label>
                            <select name="payment_country_id" id="country" required class="co-input">
                                @foreach($countries as $c)
                                <option value="{{ $c->id }}" @selected(old('payment_country_id', $authCustomer?->defaultAddress?->country_id ?? $defaultCountryId) == $c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="co-field">
                            <label class="co-label">State *</label>
                            <select name="payment_zone_id" id="zone" required class="co-input">
                                @foreach($zones as $z)
                                <option value="{{ $z->id }}" @selected(old('payment_zone_id', $authCustomer?->defaultAddress?->zone_id ?? $defaultZoneId) == $z->id)>{{ $z->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <!-- Payment + Shipping combined -->
                <div class="co-card">
                    <div class="co-card-title">
                        <div class="co-card-icon" style="background:#fef3c7;"><svg width="14" height="14" fill="none" stroke="#d97706" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg></div>
                        Payment & Shipping
                    </div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:8px;">Payment Method</div>
                    <div id="payment-methods">
                        @forelse($paymentMethods as $m)
                        <label class="co-option"><div style="display:flex;align-items:center;"><input type="radio" name="payment_method_id" value="{{ $m->id }}" @checked(old('payment_method_id')==$m->id) required><span class="co-option-label">{{ $m->name }}</span></div></label>
                        @empty<p style="font-size:13px;color:#ef4444;">No payment methods available.</p>@endforelse
                    </div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin:16px 0 8px;">Shipping Method</div>
                    <div id="shipping-methods">
                        @forelse($shippingMethods as $m)
                        @php $shipCost = $m->calculateCost($subtotal); @endphp
                        <label class="co-option"
                               @click="selectShipping({{ $shipCost }})">
                            <div style="display:flex;align-items:center;">
                                <input type="radio" name="shipping_method_id" value="{{ $m->id }}"
                                       @checked($selectedShippingId == $m->id)
                                       @change="selectShipping({{ $shipCost }})"
                                       required>
                                <span class="co-option-label">{{ $m->name }}</span>
                            </div>
                            <span class="co-option-price">${{ number_format($shipCost, 2) }}</span>
                        </label>
                        @empty
                        <p style="font-size:13px;color:#ef4444;">No shipping methods available.</p>
                        @endforelse
                    </div>
                    @if($shippingMethods->count() > 0)
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const firstShipping = document.querySelector('#shipping-methods input[name="shipping_method_id"]');
                        if (firstShipping) {
                            firstShipping.checked = true;
                            const cost = parseFloat(firstShipping.closest('label').querySelector('.co-option-price').textContent.replace(/[^0-9.]/g, '')) || 0;
                            const alpineEl = document.getElementById('checkout-root');
                            if (alpineEl && alpineEl._x_dataStack) {
                                alpineEl._x_dataStack[0].selectShipping(cost);
                            }
                        }
                    });
                    </script>
                    @endif
                </div>
                <!-- Order note — collapsible -->
                <div x-data="{ open: false }" style="margin-bottom:16px;">
                    <button type="button" @click="open=!open" class="co-note-toggle">
                        <span x-text="open ? '− Hide note' : '+ Add order note'"></span>
                    </button>
                    <div x-show="open" x-cloak style="margin-top:10px;">
                        <textarea name="comment" rows="2" placeholder="Special instructions..." class="co-input" style="resize:none;">{{ old('comment') }}</textarea>
                    </div>
                </div>
                <button type="submit" class="co-place-btn">Place Order →</button>
                <!-- Trust row -->
                <div style="display:flex;justify-content:center;gap:20px;margin-top:14px;flex-wrap:wrap;">
                    @foreach(['Free shipping $150+','Easy returns','Secure checkout'] as $t)
                    <span style="display:flex;align-items:center;gap:5px;font-size:11px;color:#9ca3af;">
                        <svg width="12" height="12" fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ $t }}
                    </span>
                    @endforeach
                </div>
            </form>
        </div>
        <!-- RIGHT: Summary -->
        <div class="co-summary">
            <div class="co-summary-title">Order Summary</div>
            @foreach($lines as $line)
            @php $p=$line['product']; $v=$line['variant']; @endphp
            <div class="co-sum-item">
                <div class="co-sum-img">
                    @if($p && $p->mainImageUrl())<img src="{{ $p->mainImageUrl() }}" alt="">@endif
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="co-sum-name">{{ html_entity_decode($p?->name ?? $v->item_sku, ENT_QUOTES, 'UTF-8') }}</div>
                    <div class="co-sum-meta">{{ $v->color }}@if($v->color && $v->size) / @endif{{ $v->size }} &bull; ×{{ $line['quantity'] }}</div>
                </div>
                <div class="co-sum-price">${{ number_format($line['total'],2) }}</div>
            </div>
            @endforeach
            <div style="margin-top:12px;">
                <div class="co-sum-row">
                    <span style="color:#6b7280;">Subtotal</span>
                    <span style="font-weight:700;">${{ number_format($subtotal, 2) }}</span>
                </div>
                <div class="co-sum-row">
                    <span style="color:#6b7280;">Shipping</span>
                    <span>
                        <span x-show="shippingCost === 0" style="color:#059669;font-weight:600;">Select method</span>
                        <span x-show="shippingCost > 0" style="font-weight:700;" x-text="'$' + shippingCost.toFixed(2)"></span>
                    </span>
                </div>
                <div class="co-sum-total">
                    <span>Total</span>
                    <span x-text="'$' + total.toFixed(2)"></span>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function getCheckoutAlpine() {
    const root = document.getElementById('checkout-root');
    return root?._x_dataStack?.[0];
}

function initShippingMethodsAfterUpdate() {
    const ship = document.getElementById('shipping-methods');
    if (typeof Alpine !== 'undefined') {
        Alpine.initTree(ship);
    }
    const firstShipping = ship.querySelector('input[name="shipping_method_id"]');
    if (firstShipping) {
        firstShipping.checked = true;
        const cost = parseFloat(firstShipping.closest('label').querySelector('.co-option-price').textContent.replace(/[^0-9.]/g, '')) || 0;
        getCheckoutAlpine()?.selectShipping(cost);
    } else {
        getCheckoutAlpine()?.selectShipping(0);
    }
}

document.getElementById('country').addEventListener('change', async function() {
    const res = await fetch('{{ route('store.checkout.zones') }}?country_id=' + this.value);
    const zones = await res.json();
    document.getElementById('zone').innerHTML = zones.map(z => `<option value="${z.id}">${z.name}</option>`).join('');
    document.getElementById('zone').dispatchEvent(new Event('change'));
});
document.getElementById('zone').addEventListener('change', async function() {
    const cId = document.getElementById('country').value;
    const res = await fetch(`{{ route('store.checkout.methods') }}?country_id=${cId}&zone_id=${this.value}`);
    const data = await res.json();
    document.getElementById('payment-methods').innerHTML = data.payment.length
        ? data.payment.map(m=>`<label class="co-option"><div style="display:flex;align-items:center;"><input type="radio" name="payment_method_id" value="${m.id}" required><span class="co-option-label">${m.name}</span></div></label>`).join('')
        : '<p style="font-size:13px;color:#ef4444;">No payment methods available.</p>';
    document.getElementById('shipping-methods').innerHTML = data.shipping.length
        ? data.shipping.map(m =>
            `<label class="co-option" @click="selectShipping(${m.cost})">
                <div style="display:flex;align-items:center;">
                    <input type="radio" name="shipping_method_id" value="${m.id}" required>
                    <span class="co-option-label">${m.name}</span>
                </div>
                <span class="co-option-price">$${Number(m.cost).toFixed(2)}</span>
            </label>`
        ).join('')
        : '<p style="font-size:13px;color:#ef4444;">No shipping methods available.</p>';
    initShippingMethodsAfterUpdate();
});
</script>
@endsection
