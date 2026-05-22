@extends('layouts.app')

@section('title', 'Shop by Category')
@section('meta_description', 'Browse sports apparel by category at Momentec.')

@push('styles')
<style>
.cats-hero {
    background: linear-gradient(135deg,#1a1a2e,#2d2b55);
    padding: 40px 0; margin-bottom: 36px;
}
.cats-hero-inner { max-width:1280px; margin:0 auto; padding:0 24px; }
.cats-hero h1 { font-size:clamp(1.8rem,3vw,2.4rem); font-weight:900; color:#fff; margin:0 0 8px; }
.cats-hero p { font-size:14px; color:rgba(255,255,255,.5); margin:0; }

.cats-container { max-width:1280px; margin:0 auto; padding:0 24px 60px; }

.cat-parent-block {
    margin-bottom: 40px;
}
.cat-parent-header {
    display: flex; align-items: center; gap: 14px;
    margin-bottom: 16px; padding-bottom: 14px;
    border-bottom: 2px solid #f1f5f9;
}
.cat-parent-letter {
    width: 48px; height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; font-weight: 900; color: #fff; flex-shrink: 0;
}
.cat-parent-info {}
.cat-parent-name {
    font-size: 1.1rem; font-weight: 800; color: #111827;
    text-decoration: none; display: block;
}
.cat-parent-name:hover { color: #4f46e5; }
.cat-parent-count { font-size: 12px; color: #9ca3af; margin-top: 2px; }

.cat-children-grid {
    display: grid;
    grid-template-columns: repeat(2,1fr);
    gap: 10px;
}
@media(min-width:640px) { .cat-children-grid { grid-template-columns: repeat(3,1fr); } }
@media(min-width:1024px) { .cat-children-grid { grid-template-columns: repeat(5,1fr); } }

.cat-child-card {
    background: #fff; border: 1.5px solid #e5e7eb;
    border-radius: 14px; padding: 14px 16px;
    text-decoration: none;
    display: flex; align-items: center; justify-content: space-between;
    transition: all .2s;
}
.cat-child-card:hover {
    border-color: #4f46e5; background: #eef2ff;
    transform: translateY(-2px); box-shadow: 0 6px 16px rgba(79,70,229,.1);
}
.cat-child-name { font-size: 13px; font-weight: 600; color: #374151; }
.cat-child-card:hover .cat-child-name { color: #4f46e5; }
.cat-child-count {
    font-size: 11px; color: #9ca3af;
    background: #f3f4f6; border-radius: 100px;
    padding: 2px 8px; white-space: nowrap;
}

.cat-standalone {
    background: #fff; border: 1.5px solid #e5e7eb;
    border-radius: 16px; padding: 18px 20px;
    text-decoration: none;
    display: flex; align-items: center; gap: 14px;
    transition: all .2s;
}
.cat-standalone:hover { border-color: #4f46e5; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(79,70,229,.1); }
.cat-standalone-name { font-size: 14px; font-weight: 700; color: #111827; }
.cat-standalone:hover .cat-standalone-name { color: #4f46e5; }
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

<div class="cats-hero">
    <div class="cats-hero-inner">
        <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:rgba(255,255,255,.4);margin-bottom:12px;">
            <a href="{{ route('home') }}" style="color:rgba(255,255,255,.4);text-decoration:none;">Home</a>
            <span>›</span>
            <span style="color:#fff;">Categories</span>
        </div>
        <h1>Shop by Category</h1>
        <p>Explore all top-level categories and their subcollections</p>
    </div>
</div>

<div class="cats-container">
    @forelse($categories as $parent)
        @php $g = $gradients[$loop->index % count($gradients)]; @endphp
        <div class="cat-parent-block">
            <div class="cat-parent-header">
                <div class="cat-parent-letter" style="background:{{ $g }};">
                    {{ strtoupper(substr($parent->name, 0, 1)) }}
                </div>
                <div class="cat-parent-info">
                    <a href="{{ route('categories.show', $parent) }}" class="cat-parent-name">
                        {{ $parent->name }}
                    </a>
                    <div class="cat-parent-count">
                        @if($parent->children->count())
                            {{ $parent->children->count() }} subcategories
                        @else
                            {{ number_format($parent->products_count) }} products
                        @endif
                    </div>
                </div>
                <a href="{{ route('categories.show', $parent) }}"
                   style="margin-left:auto;font-size:12px;font-weight:700;color:#4f46e5;text-decoration:none;white-space:nowrap;">
                    View all →
                </a>
            </div>

            @if($parent->children->count())
                <div class="cat-children-grid">
                    @foreach($parent->children->sortBy('name') as $child)
                        <a href="{{ route('categories.show', $child) }}" class="cat-child-card">
                            <span class="cat-child-name">{{ $child->name }}</span>
                            @if(isset($child->products_count))
                                <span class="cat-child-count">{{ $child->products_count }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @else
                <a href="{{ route('categories.show', $parent) }}" class="cat-standalone">
                    <div class="cat-parent-letter" style="background:{{ $g }};width:40px;height:40px;font-size:16px;border-radius:12px;">
                        {{ strtoupper(substr($parent->name, 0, 1)) }}
                    </div>
                    <span class="cat-standalone-name">{{ $parent->name }}</span>
                    <span class="cat-child-count" style="margin-left:auto;">{{ number_format($parent->products_count) }}</span>
                </a>
            @endif
        </div>
    @empty
        <p style="color:#6b7280;font-size:14px;">No categories available yet.</p>
    @endforelse
</div>
@endsection
