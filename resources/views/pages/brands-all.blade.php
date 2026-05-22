@extends('layouts.app')

@section('title', 'All Brands')
@section('meta_description', 'Browse all sports apparel brands available at Momentec.')

@section('content')
@php
    $alphabet = range('A', 'Z');
    $availableLetters = $letters ?? $brands->keys();
@endphp

<div class="mx-auto max-w-[1280px] px-4 py-8 sm:px-6 lg:px-8">
    <x-breadcrumb :items="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Brands'],
    ]" />

    <h1 class="text-3xl font-bold text-primary">All Brands</h1>
    <p class="mt-2 text-gray-600">Browse our complete directory of sports apparel brands.</p>

    {{-- A–Z filter bar --}}
    <nav class="mt-8 flex flex-wrap gap-2" aria-label="Filter brands by letter">
        <a href="#brands-top" class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary-900">All</a>
        @foreach($alphabet as $letter)
            @if($availableLetters->contains($letter))
                <a href="#letter-{{ $letter }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:border-accent hover:bg-accent-light hover:text-accent">
                    {{ $letter }}
                </a>
            @else
                <span class="rounded-lg border border-gray-100 px-3 py-1.5 text-xs font-medium text-gray-300">{{ $letter }}</span>
            @endif
        @endforeach
    </nav>

    <div id="brands-top" class="mt-10 space-y-12">
        @forelse($brands as $letter => $letterBrands)
            <section id="letter-{{ $letter }}" class="scroll-mt-28">
                <h2 class="border-b border-gray-200 pb-2 text-2xl font-bold text-primary">{{ $letter }}</h2>
                <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($letterBrands as $brand)
                        <a
                            href="{{ route('brands.show', $brand) }}"
                            class="group flex flex-col items-center rounded-xl border border-gray-100 bg-white p-5 text-center shadow-sm transition hover:-translate-y-0.5 hover:border-accent/30 hover:shadow-md"
                        >
                            <span class="text-base font-bold text-primary transition group-hover:text-accent sm:text-lg">
                                {{ $brand->name }}
                            </span>
                            <x-badge variant="muted" class="mt-3">
                                {{ number_format($brand->products_count) }} {{ Str::plural('product', $brand->products_count) }}
                            </x-badge>
                        </a>
                    @endforeach
                </div>
            </section>
        @empty
            <p class="text-gray-600">No brands available yet.</p>
        @endforelse
    </div>
</div>
@endsection
