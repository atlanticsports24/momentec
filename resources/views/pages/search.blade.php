@extends('layouts.app')

@section('title', $q ? "Search results for \"{$q}\"" : 'Search')
@section('meta_description', $q ? "Found {$products->total()} products matching \"{$q}\" on Momentec." : 'Search the Momentec sports apparel catalog.')

@section('content')
<div class="mx-auto max-w-[1280px] px-4 py-8 sm:px-6 lg:px-8">
    <x-breadcrumb :items="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Search'],
    ]" />

    <h1 class="text-3xl font-bold text-primary">Search</h1>

    <div class="mx-auto mt-6 max-w-2xl">
        <x-search-bar :value="$q" :large="true" />
    </div>

    @if($q !== '')
        <p class="mt-8 text-center text-lg text-gray-700">
            <span class="font-semibold text-primary">{{ number_format($products->total()) }}</span>
            {{ Str::plural('result', $products->total()) }} for
            <span class="font-semibold">&ldquo;{{ $q }}&rdquo;</span>
        </p>
    @endif

    @if($products->count())
        <div class="mt-10 grid grid-cols-1 gap-6 xs:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            @foreach($products as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
        <div class="mt-10">
            {{ $products->appends(['q' => $q])->links('components.pagination') }}
        </div>
    @else
        <div class="mx-auto mt-12 max-w-lg rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <h2 class="mt-4 text-xl font-semibold text-primary">
                @if($q !== '')
                    No results found for &ldquo;{{ $q }}&rdquo;
                @else
                    Enter a search term
                @endif
            </h2>
            <p class="mt-2 text-gray-600">Try different keywords, or explore popular brands and categories below.</p>

            @if($suggestedBrands->isNotEmpty())
                <div class="mt-8 text-left">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Popular brands</h3>
                    <ul class="mt-3 space-y-2">
                        @foreach($suggestedBrands as $brand)
                            <li>
                                <a href="{{ route('brands.show', $brand) }}" class="text-sm font-medium text-accent hover:underline">
                                    {{ $brand->name }} ({{ $brand->products_count }})
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($suggestedCategories->isNotEmpty())
                <div class="mt-6 text-left">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Top categories</h3>
                    <ul class="mt-3 space-y-2">
                        @foreach($suggestedCategories as $category)
                            <li>
                                <a href="{{ route('categories.show', $category) }}" class="text-sm font-medium text-accent hover:underline">
                                    {{ $category->name }} ({{ $category->products_count }})
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <a href="{{ route('products.index') }}" class="mt-8 inline-flex rounded-lg bg-accent px-6 py-3 text-sm font-semibold text-white hover:bg-accent-dark">
                Browse all products
            </a>
        </div>
    @endif
</div>
@endsection
