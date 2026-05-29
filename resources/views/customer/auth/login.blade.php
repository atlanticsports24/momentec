@extends('layouts.app')

@section('title', 'Sign In')

@push('styles')
<style>
.auth-wrap { max-width:420px; margin:48px auto 80px; padding:0 24px; }
.auth-card { background:#fff; border:1.5px solid #e5e7eb; border-radius:20px; padding:32px 28px; }
.auth-title { font-size:1.35rem; font-weight:900; color:#111827; margin:0 0 6px; }
.auth-sub { font-size:13px; color:#6b7280; margin:0 0 24px; }
.co-label { display:block; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; margin-bottom:5px; }
.co-input { width:100%; border:1.5px solid #e5e7eb; border-radius:10px; padding:10px 13px; font-size:14px; color:#111827; outline:none; background:#fff; box-sizing:border-box; }
.co-input:focus { border-color:#4f46e5; }
.co-field { margin-bottom:14px; }
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
        <h1 class="auth-title">Welcome back</h1>
        <p class="auth-sub">Sign in to your Momentec account</p>

        @if($errors->any())
        <div class="auth-error">{{ $errors->first() }}</div>
        @endif

        @if(session('success'))
        <div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:10px;padding:10px 14px;font-size:13px;color:#065f46;margin-bottom:16px;">{{ session('success') }}</div>
        @endif

        <form action="{{ route('customer.login.post') }}" method="POST">
            @csrf
            <input type="hidden" name="redirect" value="{{ request('redirect') }}">
            <div class="co-field">
                <label class="co-label" for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus class="co-input" placeholder="you@email.com">
            </div>
            <div class="co-field">
                <label class="co-label" for="password">Password</label>
                <input type="password" id="password" name="password" required class="co-input" placeholder="••••••••">
            </div>
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#374151;margin-bottom:8px;cursor:pointer;">
                <input type="checkbox" name="remember" value="1" style="accent-color:#4f46e5;">
                Remember me
            </label>
            <button type="submit" class="auth-btn">Sign In</button>
        </form>

        <p class="auth-footer">
            Don't have an account?
            <a href="{{ route('customer.register', request()->only('redirect')) }}">Create one →</a>
        </p>
    </div>
</div>
@endsection
