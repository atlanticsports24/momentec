@extends('layouts.app')

@section('title', 'Create Account')

@push('styles')
<style>
.auth-wrap { max-width:460px; margin:48px auto 80px; padding:0 24px; }
.auth-card { background:#fff; border:1.5px solid #e5e7eb; border-radius:20px; padding:32px 28px; }
.auth-title { font-size:1.35rem; font-weight:900; color:#111827; margin:0 0 6px; }
.auth-sub { font-size:13px; color:#6b7280; margin:0 0 24px; }
.co-label { display:block; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; margin-bottom:5px; }
.co-input { width:100%; border:1.5px solid #e5e7eb; border-radius:10px; padding:10px 13px; font-size:14px; color:#111827; outline:none; background:#fff; box-sizing:border-box; }
.co-input:focus { border-color:#4f46e5; }
.co-field { margin-bottom:14px; }
.co-row-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
@media(max-width:480px) { .co-row-2 { grid-template-columns:1fr; } }
.auth-btn { width:100%; background:#111827; color:#fff; border:none; border-radius:12px; padding:13px; font-size:14px; font-weight:800; cursor:pointer; margin-top:8px; }
.auth-btn:hover { background:#1f2937; }
.auth-footer { text-align:center; font-size:13px; color:#6b7280; margin-top:20px; }
.auth-footer a { color:#4f46e5; font-weight:600; text-decoration:none; }
.auth-error { background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:10px 14px; font-size:13px; color:#dc2626; margin-bottom:16px; }
</style>
@endpush

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <h1 class="auth-title">Create account</h1>
        <p class="auth-sub">Join Momentec for faster checkout and order tracking</p>

        @if($errors->any())
        <div class="auth-error">
            <ul style="margin:0;padding:0 0 0 16px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form action="{{ route('customer.register.post') }}" method="POST">
            @csrf
            <input type="hidden" name="redirect" value="{{ request('redirect') }}">
            <div class="co-row-2">
                <div class="co-field">
                    <label class="co-label" for="firstname">First name</label>
                    <input type="text" id="firstname" name="firstname" value="{{ old('firstname') }}" required class="co-input">
                </div>
                <div class="co-field">
                    <label class="co-label" for="lastname">Last name</label>
                    <input type="text" id="lastname" name="lastname" value="{{ old('lastname') }}" required class="co-input">
                </div>
            </div>
            <div class="co-field">
                <label class="co-label" for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required class="co-input">
            </div>
            <div class="co-field">
                <label class="co-label" for="telephone">Phone (optional)</label>
                <input type="text" id="telephone" name="telephone" value="{{ old('telephone') }}" class="co-input">
            </div>
            <div class="co-field">
                <label class="co-label" for="password">Password</label>
                <input type="password" id="password" name="password" required class="co-input" placeholder="Min. 8 characters">
            </div>
            <div class="co-field">
                <label class="co-label" for="password_confirmation">Confirm password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required class="co-input">
            </div>
            <button type="submit" class="auth-btn">Create Account</button>
        </form>

        <p class="auth-footer">
            Already have an account?
            <a href="{{ route('customer.login', request()->only('redirect')) }}">Sign in →</a>
        </p>
    </div>
</div>
@endsection
