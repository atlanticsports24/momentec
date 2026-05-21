@extends('layouts.app')

@section('title', 'Shop by Category')
@section('meta_description', 'Browse sports apparel by category at Momentec.')

@section('content')
@php
    $categoryGradients = [
        'from-indigo-600 to-violet-700',
        'from-emerald-600 to-teal-700',
        'from-orange-500 to-amber-600',
        'from-rose-600 to-pink-700',
        'from-sky-600 to-blue-700',
        'from-violet-600 to-purple-700',
        'from-lime-600 to-green-700',
        'from-slate-700 to-primary',
    ];
@endphp

<div class="mx-auto max-w-[1280px] px-4 py-8 sm:px-6 lg:px-8">
    <x-breadcrumb :items="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Categories'],
    ]" />

    <h1 class="text-3xl font-bold text-primary">Shop by Category</h1>
    <p class="mt-2 text-gray-600">Explore top-level categories and their subcategories.</p>

    <div class="mt-10 grid grid-cols-1 gap-8 md:grid-cols-2">
        @forelse($categories as $parent)
            @php $gradient = $categoryGradients[$loop->index % count($categoryGradients)]; @endphp
            <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <a
                    href="{{ route('categories.show', $parent) }}"
                    class="block bg-gradient-to-br {{ $gradient }} p-6 text-white transition hover:opacity-95"
                >
                    <h2 class="text-xl font-bold">{{ $parent->name }}</h2>
                    <p class="mt-1 text-sm text-white/80">{{ number_format($parent->products_count) }} products</p>
                </a>
                @if($parent->children->isNotEmpty())
                    <ul class="divide-y divide-gray-100 p-4">
                        @foreach($parent->children as $child)
                            <li>
                                <a href="{{ route('categories.show', $child) }}" class="flex items-center justify-between py-2.5 text-sm text-gray-700 transition hover:text-accent">
                                    <span class="font-medium">{{ $child->name }}</span>
                                    <span class="text-xs text-gray-400">{{ $child->products_count }} products</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </article>
        @empty
            <p class="text-gray-600">No categories available yet.</p>
        @endforelse
    </div>
</div>
@endsection
