@extends('layouts.app')

@section('content')
<div style="max-width:1280px;margin:0 auto;padding:32px 24px 60px;display:grid;grid-template-columns:240px 1fr;gap:24px;align-items:start;">
    <aside style="position:sticky;top:80px;background:#fff;border:1.5px solid #e5e7eb;border-radius:18px;overflow:hidden;">
        <div style="background:linear-gradient(135deg,#1a1a2e,#2d2b55);padding:20px;">
            <div style="width:48px;height:48px;border-radius:50%;background:#4f46e5;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:900;color:#fff;margin-bottom:10px;">
                {{ strtoupper(substr(auth('customer')->user()->firstname, 0, 1)) }}
            </div>
            <div style="font-size:14px;font-weight:700;color:#fff;">{{ auth('customer')->user()->full_name }}</div>
            <div style="font-size:12px;color:rgba(255,255,255,.5);">{{ auth('customer')->user()->email }}</div>
        </div>
        <nav style="padding:8px;">
            @php
            $navItems = [
                ['route' => 'customer.dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'Dashboard'],
                ['route' => 'customer.orders', 'routes' => 'customer.order.detail', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'label' => 'My Orders'],
                ['route' => 'customer.wishlist', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', 'label' => 'Wishlist'],
                ['route' => 'customer.addresses', 'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z', 'label' => 'Addresses'],
                ['route' => 'customer.account', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'label' => 'Profile'],
            ];
            @endphp
            @foreach($navItems as $item)
            <a href="{{ route($item['route']) }}"
               style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;margin-bottom:2px;transition:all .15s;
               {{ request()->routeIs($item['route']) || (isset($item['routes']) && request()->routeIs($item['routes'])) ? 'background:#eef2ff;color:#4f46e5;' : 'color:#374151;' }}"
               onmouseover="if(!this.dataset.active)this.style.background='#f9fafb'"
               onmouseout="if(!this.dataset.active)this.style.background='transparent'"
               @if(request()->routeIs($item['route']) || (isset($item['routes']) && request()->routeIs($item['routes']))) data-active="1" @endif>
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                {{ $item['label'] }}
            </a>
            @endforeach
            <form action="{{ route('customer.logout') }}" method="POST" style="margin-top:8px;padding-top:8px;border-top:1px solid #f1f5f9;">
                @csrf
                <button type="submit" style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;font-size:13px;font-weight:600;color:#ef4444;background:none;border:none;cursor:pointer;width:100%;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </button>
            </form>
        </nav>
    </aside>

    <main>
        @if(session('success'))
        <div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:12px;padding:12px 16px;margin-bottom:20px;font-size:13px;font-weight:600;color:#065f46;">{{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#dc2626;">
            <ul style="margin:0;padding:0 0 0 16px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif
        @yield('dashboard_content')
    </main>
</div>
@endsection
