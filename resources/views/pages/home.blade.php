@extends('layouts.app')

@section('title', 'Momentec — Sports Apparel Catalog')
@section('meta_description', 'Shop top sports apparel brands. Browse products by brand, category, color and size.')

@section('schema_json')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "WebSite",
    "name": "Momentec",
    "url": "{{ url('/') }}"
}
</script>
@endsection

@push('styles')
<style>
/* ── Reset & Base ── */
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

/* ── Animations ── */
@keyframes float1 { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
@keyframes float2 { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
@keyframes float3 { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-6px)} }
.f1{animation:float1 4s ease-in-out infinite}
.f2{animation:float2 5s ease-in-out infinite 1s}
.f3{animation:float3 3.5s ease-in-out infinite .5s}

/* ── Announcement ── */
.ann-bar {
    background: #4f46e5; color: #fff;
    text-align: center; padding: 10px 16px;
    font-size: 13px; font-weight: 600;
    position: relative;
}
.ann-bar a { color: #fff; text-decoration: underline; }
.ann-close {
    position: absolute; right: 16px; top: 50%; transform: translateY(-50%);
    background: none; border: none; color: rgba(255,255,255,.7);
    cursor: pointer; font-size: 18px; line-height: 1;
    padding: 4px 8px;
}
.ann-close:hover { color: #fff; }

/* ── Flash Bar ── */
.flash-bar {
    background: linear-gradient(90deg, #ea580c, #e11d48);
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 24px;
}
.flash-bar-left { display: flex; align-items: center; gap: 8px; color: #fff; font-size: 14px; font-weight: 700; }
.flash-timer { display: flex; align-items: center; gap: 4px; font-size: 14px; font-weight: 700; color: #fff; font-family: monospace; }
.flash-timer span.block {
    background: rgba(0,0,0,.25); border-radius: 6px;
    padding: 2px 8px; min-width: 36px; text-align: center;
}
.flash-timer .sep { color: rgba(255,255,255,.6); }
.flash-all { color: #fff; font-size: 13px; font-weight: 700; text-decoration: none; }
.flash-all:hover { text-decoration: underline; }

/* ── Section Layout ── */
.sec { padding: 60px 0; }
.sec-sm { padding: 40px 0; }
.sec-dark { background: #0a0918; }
.sec-light { background: #f8fafc; }
.sec-white { background: #fff; }
.container {
    max-width: 1280px; margin: 0 auto;
    padding: 0 24px;
}
.sec-head { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 28px; }
.sec-title { font-size: 1.4rem; font-weight: 800; color: #111827; }
.sec-title-icon { margin-right: 8px; }
.sec-sub { font-size: 13px; color: #9ca3af; margin-top: 2px; }
.sec-link { font-size: 13px; font-weight: 700; color: #4f46e5; text-decoration: none; display: flex; align-items: center; gap: 4px; white-space: nowrap; }
.sec-link:hover { color: #3730a3; }

/* ── Horizontal Scroll Row ── */
.h-scroll { display: flex; gap: 16px; overflow-x: auto; padding-bottom: 8px; -webkit-overflow-scrolling: touch; }

/* ── Mini Product Card ── */
.mini-card {
    width: 168px; flex-shrink: 0;
    background: #fff; border-radius: 16px;
    border: 1px solid #e5e7eb;
    overflow: hidden; text-decoration: none;
    transition: transform .25s, box-shadow .25s;
}
.mini-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,.1); }
.mini-card-img { width: 100%; aspect-ratio: 1/1; object-fit: cover; background: #f3f4f6; display: block; transition: transform .4s; }
.mini-card:hover .mini-card-img { transform: scale(1.06); }
.mini-card-img-wrap { overflow: hidden; }
.mini-card-body { padding: 10px 12px 14px; }
.mini-card-brand { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #4f46e5; }
.mini-card-name { font-size: 12px; font-weight: 600; color: #1f2937; line-height: 1.4; margin: 3px 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.mini-card-price { font-size: 13px; font-weight: 800; color: #111827; }

/* ── Category Grid ── */
.cat-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
@media(min-width:640px) { .cat-grid { grid-template-columns: repeat(4, 1fr); } }
@media(min-width:1024px) { .cat-grid { grid-template-columns: repeat(7, 1fr); } }

.cat-card {
    border-radius: 18px; overflow: hidden;
    text-decoration: none;
    background: #fff;
    border: 1px solid #e5e7eb;
    transition: transform .3s, box-shadow .3s;
}
.cat-card:hover { transform: translateY(-5px); box-shadow: 0 16px 40px rgba(0,0,0,.12); }
.cat-top {
    height: 110px; position: relative;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
}
.cat-top::before {
    content: ''; position: absolute;
    top: -20px; right: -20px;
    width: 80px; height: 80px; border-radius: 50%;
    background: rgba(255,255,255,.12);
}
.cat-top::after {
    content: ''; position: absolute;
    bottom: -10px; left: -10px;
    width: 50px; height: 50px; border-radius: 50%;
    background: rgba(0,0,0,.08);
}
.cat-letter {
    width: 52px; height: 52px; border-radius: 14px;
    background: rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; font-weight: 900; color: #fff;
    position: relative; z-index: 1;
    transition: transform .3s;
    backdrop-filter: blur(4px);
    border: 1.5px solid rgba(255,255,255,.3);
}
.cat-card:hover .cat-letter { transform: scale(1.1) rotate(-5deg); }
.cat-bottom { padding: 10px 10px 12px; text-align: center; }
.cat-name { font-size: 12px; font-weight: 700; color: #111827; line-height: 1.3; }
.cat-count { font-size: 10px; color: #9ca3af; margin-top: 2px; }

/* ── Brand Grid ── */
.brand-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
@media(min-width:480px) { .brand-grid { grid-template-columns: repeat(3, 1fr); } }
@media(min-width:640px) { .brand-grid { grid-template-columns: repeat(4, 1fr); } }
@media(min-width:1024px) { .brand-grid { grid-template-columns: repeat(8, 1fr); } }

.brand-card {
    border-radius: 16px; border: 1.5px solid #e5e7eb;
    background: #f9fafb; padding: 16px 10px;
    text-align: center; text-decoration: none;
    transition: border-color .2s, box-shadow .2s, background .2s, transform .2s;
}
.brand-card:hover { border-color: #4f46e5; background: #eef2ff; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(79,70,229,.12); }
.brand-ltr {
    width: 44px; height: 44px; border-radius: 12px;
    background: #fff; margin: 0 auto 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; font-weight: 900; color: #4f46e5;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
    transition: transform .2s;
}
.brand-card:hover .brand-ltr { transform: scale(1.15); }
.brand-name { font-size: 11px; font-weight: 700; color: #1f2937; line-height: 1.3; }
.brand-count { font-size: 10px; color: #9ca3af; margin-top: 2px; }
.brand-cta-wrap { text-align: center; margin-top: 28px; }
.brand-cta {
    display: inline-flex; align-items: center; gap: 8px;
    border: 2px solid #4f46e5; color: #4f46e5;
    padding: 11px 28px; border-radius: 12px;
    font-size: 14px; font-weight: 700; text-decoration: none;
    transition: background .2s, color .2s;
}
.brand-cta:hover { background: #4f46e5; color: #fff; }

/* ── Promo Banners ── */
.promo-grid { display: grid; grid-template-columns: 1fr; gap: 20px; }
@media(min-width:768px) { .promo-grid { grid-template-columns: repeat(3, 1fr); } }
.promo-card {
    border-radius: 24px; overflow: hidden;
    min-height: 240px; position: relative;
    text-decoration: none;
    transition: transform .3s, box-shadow .3s;
}
.promo-card:hover { transform: translateY(-5px); box-shadow: 0 20px 50px rgba(0,0,0,.2); }
.promo-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.promo-overlay { position: absolute; inset: 0; }
.promo-body { position: relative; z-index: 1; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; padding: 28px; }
.promo-tag {
    display: inline-block; border-radius: 100px;
    background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25);
    padding: 4px 14px; font-size: 11px; font-weight: 700;
    color: #fff; margin-bottom: 10px;
    backdrop-filter: blur(4px);
}
.promo-title { font-size: 1.4rem; font-weight: 900; color: #fff; line-height: 1.2; }
.promo-sub { font-size: 13px; color: rgba(255,255,255,.75); margin: 6px 0 16px; }
.promo-btn {
    display: inline-flex; align-items: center; gap: 6px;
    border: 1.5px solid rgba(255,255,255,.3);
    background: rgba(255,255,255,.1);
    backdrop-filter: blur(6px);
    color: #fff; padding: 9px 20px; border-radius: 10px;
    font-size: 13px; font-weight: 700;
    transition: background .2s;
    width: fit-content;
}
.promo-btn:hover { background: rgba(255,255,255,.22); }

/* ── Featured Products Grid ── */
.prod-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
@media(min-width:768px) { .prod-grid { grid-template-columns: repeat(3, 1fr); } }
@media(min-width:1024px) { .prod-grid { grid-template-columns: repeat(4, 1fr); } }

/* ── Why Choose Us ── */
.why-grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
@media(min-width:640px) { .why-grid { grid-template-columns: repeat(2, 1fr); } }
@media(min-width:1024px) { .why-grid { grid-template-columns: repeat(4, 1fr); } }
.why-card {
    background: #f9fafb; border: 1.5px solid #e5e7eb;
    border-radius: 20px; padding: 32px 24px; text-align: center;
    transition: border-color .25s, transform .25s, box-shadow .25s;
}
.why-card:hover { border-color: rgba(79,70,229,.3); transform: translateY(-4px); box-shadow: 0 12px 32px rgba(79,70,229,.08); }
.why-icon {
    width: 56px; height: 56px; border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px;
}
.why-icon svg { width: 28px; height: 28px; }
.why-title { font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 8px; }
.why-text { font-size: 13px; line-height: 1.65; color: #6b7280; }

/* ── Section Header Centered ── */
.sec-head-center { text-align: center; margin-bottom: 36px; }
.sec-head-center .sec-title { font-size: 1.6rem; }
.sec-title-lg { font-size: clamp(1.4rem, 3vw, 1.9rem); font-weight: 800; color: #111827; }
</style>
@endpush

@section('content')
@php
$gradients = [
    'linear-gradient(135deg,#4f46e5,#7c3aed)',
    'linear-gradient(135deg,#059669,#0891b2)',
    'linear-gradient(135deg,#ea580c,#d97706)',
    'linear-gradient(135deg,#db2777,#9333ea)',
    'linear-gradient(135deg,#0284c7,#4f46e5)',
    'linear-gradient(135deg,#7c3aed,#db2777)',
    'linear-gradient(135deg,#16a34a,#059669)',
    'linear-gradient(135deg,#1e293b,#334155)',
];
@endphp

{{-- ── ANNOUNCEMENT BAR ── --}}
<div x-data="{ show: !localStorage.getItem('ann_v1') }" x-show="show" x-cloak>
    <div class="ann-bar">
        🔥 Free shipping on orders over $150 —
        <a href="{{ route('products.index') }}">Shop Now →</a>
        <button class="ann-close" @click="show=false;localStorage.setItem('ann_v1','1')" aria-label="Close">×</button>
    </div>
</div>

<section style="position:relative;width:100%;height:450px;overflow:hidden;background:#0a0918;"
    x-data="{
        current: 0,
        total: 3,
        timer: null,
        init() { this.timer = setInterval(() => { this.current = (this.current + 1) % this.total }, 5000) },
        go(i) { this.current = i; clearInterval(this.timer); this.timer = setInterval(() => { this.current = (this.current + 1) % this.total }, 5000) }
    }">
    <!-- Slide 1 -->
    <div style="position:absolute;inset:0;transition:opacity .6s ease;"
         :style="current===0 ? 'opacity:1;z-index:2' : 'opacity:0;z-index:1'">
        <img src="{{ asset('images/hero-1.jpg') }}"
             alt="Banner 1"
             loading="eager"
             fetchpriority="high"
             onerror="this.src='https://images.unsplash.com/photo-1556821840-3a63f15732ce?w=1600&h=450&fit=crop&fm=webp&q=80'"
             style="width:100%;height:100%;object-fit:cover;display:block;">
    </div>
    <!-- Slide 2 -->
    <div style="position:absolute;inset:0;transition:opacity .6s ease;"
         :style="current===1 ? 'opacity:1;z-index:2' : 'opacity:0;z-index:1'">
        <img src="{{ asset('images/hero-2.jpg') }}"
             alt="Banner 2"
             loading="lazy"
             onerror="this.src='https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=1600&h=450&fit=crop&fm=webp&q=80'"
             style="width:100%;height:100%;object-fit:cover;display:block;">
    </div>
    <!-- Slide 3 -->
    <div style="position:absolute;inset:0;transition:opacity .6s ease;"
         :style="current===2 ? 'opacity:1;z-index:2' : 'opacity:0;z-index:1'">
        <img src="{{ asset('images/hero-1.jpg') }}"
             alt="Banner 3"
             loading="lazy"
             onerror="this.src='https://images.unsplash.com/photo-1552674605-db6ffd4facb5?w=1600&h=450&fit=crop&fm=webp&q=80'"
             style="width:100%;height:100%;object-fit:cover;display:block;">
    </div>
    <!-- Prev -->
    <button @click="go((current-1+total)%total)"
            style="position:absolute;left:16px;top:50%;transform:translateY(-50%);z-index:10;width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.3);color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;backdrop-filter:blur(8px);transition:background .2s;"
            onmouseover="this.style.background='rgba(255,255,255,.35)'"
            onmouseout="this.style.background='rgba(255,255,255,.2)'"
            aria-label="Previous">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
    </button>
    <!-- Next -->
    <button @click="go((current+1)%total)"
            style="position:absolute;right:16px;top:50%;transform:translateY(-50%);z-index:10;width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.3);color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;backdrop-filter:blur(8px);transition:background .2s;"
            onmouseover="this.style.background='rgba(255,255,255,.35)'"
            onmouseout="this.style.background='rgba(255,255,255,.2)'"
            aria-label="Next">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
    </button>
    <!-- Dots -->
    <div style="position:absolute;bottom:14px;left:50%;transform:translateX(-50%);z-index:10;display:flex;gap:8px;align-items:center;">
        <template x-for="i in total" :key="i">
            <button @click="go(i-1)"
                    style="border:none;cursor:pointer;border-radius:100px;transition:all .3s;"
                    :style="current===i-1 ? 'width:22px;height:6px;background:#fff;' : 'width:6px;height:6px;background:rgba(255,255,255,.45);'"
                    :aria-label="'Slide '+i">
            </button>
        </template>
    </div>
</section>

{{-- ── FLASH BAR ── --}}
<div class="flash-bar"
     x-data="{s:86400,get h(){return String(Math.floor(this.s/3600)).padStart(2,'0')},get m(){return String(Math.floor((this.s%3600)/60)).padStart(2,'0')},get sec(){return String(this.s%60).padStart(2,'0')},init(){setInterval(()=>{this.s>0?this.s--:this.s=86400},1000)}}">
    <div class="flash-bar-left">
        <span>⚡</span>
        <span>Flash Deals</span>
    </div>
    <div class="flash-timer">
        <span class="block" x-text="h"></span>
        <span class="sep">:</span>
        <span class="block" x-text="m"></span>
        <span class="sep">:</span>
        <span class="block" x-text="sec"></span>
    </div>
    <a href="{{ route('products.index') }}" class="flash-all">SEE ALL →</a>
</div>

{{-- ── NEW ARRIVALS ── --}}
@if($featuredProducts->count())
<section class="sec-sm sec-white">
    <div class="container">
        <div class="sec-head">
            <div>
                <div class="sec-title"><span class="sec-title-icon">🆕</span> New Arrivals</div>
            </div>
            <a href="{{ route('products.index') }}" class="sec-link">
                View All
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="h-scroll scrollbar-hide">
            @foreach($featuredProducts->take(12) as $p)
            @php $img = $p->mainImageUrl() ?? 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&q=80'; @endphp
            <a href="{{ route('products.show', $p) }}" class="mini-card">
                <div class="mini-card-img-wrap">
                    <img src="{{ $img }}" alt="{{ $p->name }}" loading="lazy" class="mini-card-img">
                </div>
                <div class="mini-card-body">
                    @if($p->brand)<div class="mini-card-brand">{{ $p->brand->name }}</div>@endif
                    <div class="mini-card-name">{{ $p->name }}</div>
                    @if($p->min_msrp)<div class="mini-card-price">From ${{ number_format($p->min_msrp,2) }}</div>@endif
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── SHOP BY CATEGORY ── --}}
@if($categories->count())
<section class="sec-sm sec-light">
    <div class="container">
        <div class="sec-head">
            <div>
                <div class="sec-title">Shop by Category</div>
                <div class="sec-sub">Browse all collections</div>
            </div>
            <a href="{{ route('categories.index') }}" class="sec-link">
                View All
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="cat-grid">
            @foreach($categories->sortBy('name') as $cat)
            @php $g = $gradients[$loop->index % count($gradients)]; @endphp
            <a href="{{ route('categories.show', $cat) }}" class="cat-card">
                <div class="cat-top" style="background:{{ $g }};">
                    <div class="cat-letter">{{ strtoupper(substr($cat->name,0,1)) }}</div>
                </div>
                <div class="cat-bottom">
                    <div class="cat-name">{{ $cat->name }}</div>
                    @if($cat->children->count())
                    <div class="cat-count">{{ $cat->children->count() }} sub</div>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── TOP BRANDS ── --}}
@if($brands->count())
<section class="sec sec-white">
    <div class="container">
        <div class="sec-head-center">
            <div class="sec-title-lg">Top Brands</div>
            <div class="sec-sub" style="margin-top:6px;">Shop from the industry's leading manufacturers</div>
        </div>
        <div class="brand-grid">
            @foreach($brands as $brand)
            <a href="{{ route('brands.show', $brand) }}" class="brand-card">
                <div class="brand-ltr">{{ strtoupper(substr($brand->name,0,1)) }}</div>
                <div class="brand-name">{{ $brand->name }}</div>
                @if(isset($brand->products_count) && $brand->products_count > 0)
                <div class="brand-count">{{ $brand->products_count }} items</div>
                @endif
            </a>
            @endforeach
        </div>
        <div class="brand-cta-wrap">
            <a href="{{ route('brands.index') }}" class="brand-cta">
                View all brands
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>
@endif

{{-- ── RECOMMENDED ── --}}
@if($featuredProducts->count())
<section class="sec-sm sec-light">
    <div class="container">
        <div class="sec-head">
            <div class="sec-title"><span class="sec-title-icon">✨</span> Recommended For You</div>
            <a href="{{ route('products.index') }}" class="sec-link">
                View All
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="h-scroll scrollbar-hide">
            @foreach($featuredProducts->reverse()->take(10) as $p)
            @php $img = $p->mainImageUrl() ?? 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&q=80'; @endphp
            <a href="{{ route('products.show', $p) }}" class="mini-card">
                <div class="mini-card-img-wrap">
                    <img src="{{ $img }}" alt="{{ $p->name }}" loading="lazy" class="mini-card-img">
                </div>
                <div class="mini-card-body">
                    @if($p->brand)<div class="mini-card-brand">{{ $p->brand->name }}</div>@endif
                    <div class="mini-card-name">{{ $p->name }}</div>
                    @if($p->min_msrp)<div class="mini-card-price">From ${{ number_format($p->min_msrp,2) }}</div>@endif
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── PROMO BANNERS ── --}}
<section class="sec sec-white">
    <div class="container">
        <div class="promo-grid">

            <div class="promo-card">
                <img src="https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=700&q=80" alt="New Season" loading="lazy" class="promo-img">
                <div class="promo-overlay" style="background:linear-gradient(135deg,rgba(49,46,129,.88),rgba(109,40,217,.75))"></div>
                <div class="promo-body">
                    <span class="promo-tag">New Season</span>
                    <div class="promo-title">Fresh Arrivals</div>
                    <div class="promo-sub">Fresh styles for every sport and season.</div>
                    <a href="{{ route('products.index') }}" class="promo-btn">Shop Now →</a>
                </div>
            </div>

            <div class="promo-card">
                <img src="https://images.unsplash.com/photo-1552674605-db6ffd4facb5?w=700&q=80" alt="Bulk Orders" loading="lazy" class="promo-img">
                <div class="promo-overlay" style="background:linear-gradient(135deg,rgba(6,78,59,.88),rgba(6,95,70,.75))"></div>
                <div class="promo-body">
                    <span class="promo-tag">B2B Pricing</span>
                    <div class="promo-title">Bulk Discounts</div>
                    <div class="promo-sub">Volume pricing for teams and retailers.</div>
                    <a href="{{ route('products.index') }}" class="promo-btn">Learn More →</a>
                </div>
            </div>

            <div class="promo-card">
                <img src="https://images.unsplash.com/photo-1517649763962-0c623066013b?w=700&q=80" alt="Brand Stores" loading="lazy" class="promo-img">
                <div class="promo-overlay" style="background:linear-gradient(135deg,rgba(124,45,18,.88),rgba(190,18,60,.75))"></div>
                <div class="promo-body">
                    <span class="promo-tag">Top Brands</span>
                    <div class="promo-title">Brand Stores</div>
                    <div class="promo-sub">Explore dedicated shops from top labels.</div>
                    <a href="{{ route('brands.index') }}" class="promo-btn">Browse Brands →</a>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ── FEATURED PRODUCTS GRID ── --}}
@if($featuredProducts->count())
<section class="sec sec-light">
    <div class="container">
        <div class="sec-head">
            <div>
                <div class="sec-title">Featured Products</div>
                <div class="sec-sub">Hand-picked from our full catalog</div>
            </div>
            <a href="{{ route('products.index') }}" class="sec-link">
                View all
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="prod-grid">
            @foreach($featuredProducts->take(8) as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── WHY CHOOSE US ── --}}
<section class="sec sec-white">
    <div class="container">
        <div class="sec-head-center">
            <div class="sec-title-lg">Why Choose Momentec</div>
            <div class="sec-sub" style="margin-top:6px;">Everything you need in a modern B2B catalog</div>
        </div>
        <div class="why-grid">

            <div class="why-card">
                <div class="why-icon" style="background:#eef2ff;">
                    <svg fill="none" stroke="#4f46e5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
                <div class="why-title">Quality Brands</div>
                <div class="why-text">Curated selection of trusted sports apparel manufacturers and labels.</div>
            </div>

            <div class="why-card">
                <div class="why-icon" style="background:#d1fae5;">
                    <svg fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                </div>
                <div class="why-title">Fast Shipping</div>
                <div class="why-text">Free shipping on orders over $150. Quick processing on all bulk orders.</div>
            </div>

            <div class="why-card">
                <div class="why-icon" style="background:#dbeafe;">
                    <svg fill="none" stroke="#2563eb" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <div class="why-title">Easy Returns</div>
                <div class="why-text">Hassle-free return policy so you can order with complete confidence.</div>
            </div>

            <div class="why-card">
                <div class="why-icon" style="background:#ede9fe;">
                    <svg fill="none" stroke="#7c3aed" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div class="why-title">Expert Support</div>
                <div class="why-text">Dedicated team to help with catalog questions and bulk orders.</div>
            </div>

        </div>
    </div>
</section>

{{-- ── NEWSLETTER ── --}}
<section style="background:#0a0918;padding:80px 0;position:relative;overflow:hidden;">
    <div style="position:absolute;inset:0;background-image:radial-gradient(rgba(255,255,255,.05) 1px,transparent 1px);background-size:24px 24px;"></div>
    <div style="position:absolute;top:-60px;left:-60px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(79,70,229,.15) 0%,transparent 70%);"></div>
    <div style="position:absolute;bottom:-60px;right:-60px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(139,92,246,.12) 0%,transparent 70%);"></div>

    <div class="container" style="position:relative;z-index:1;text-align:center;">
        <div style="max-width:560px;margin:0 auto;">
            <span style="display:inline-block;background:rgba(79,70,229,.2);border:1px solid rgba(79,70,229,.3);border-radius:100px;padding:5px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#818cf8;margin-bottom:16px;">Newsletter</span>
            <h2 style="font-size:clamp(1.8rem,4vw,2.4rem);font-weight:900;color:#fff;margin:0 0 12px;">Stay in the Loop</h2>
            <p style="font-size:14px;color:#64748b;line-height:1.7;margin:0 0 32px;">Get new product drops, brand updates, and catalog news delivered to your inbox.</p>

            @if(session('newsletter_success'))
            <div style="background:rgba(5,150,105,.1);border:1px solid rgba(5,150,105,.2);border-radius:12px;padding:14px 20px;color:#34d399;font-size:14px;font-weight:600;margin-bottom:20px;">
                ✅ {{ session('newsletter_success') }}
            </div>
            @endif

            <form action="{{ route('newsletter') }}" method="POST" style="display:flex;gap:10px;flex-wrap:wrap;">
                @csrf
                <input type="email" name="email" value="{{ old('email') }}" required
                       placeholder="you@company.com"
                       style="flex:1;min-width:200px;background:rgba(255,255,255,.06);border:1.5px solid rgba(255,255,255,.1);border-radius:12px;padding:13px 18px;font-size:14px;color:#fff;outline:none;"
                       onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='rgba(255,255,255,.1)'">
                <button type="submit"
                        style="background:#4f46e5;color:#fff;border:none;border-radius:12px;padding:13px 28px;font-size:14px;font-weight:700;cursor:pointer;transition:background .2s;white-space:nowrap;"
                        onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
                    Subscribe
                </button>
            </form>

            @error('email')
            <p style="color:#f87171;font-size:13px;margin-top:10px;">{{ $message }}</p>
            @enderror

            <p style="font-size:12px;color:#374151;margin-top:14px;">No spam, ever. Unsubscribe at any time.</p>
        </div>
    </div>
</section>

@endsection