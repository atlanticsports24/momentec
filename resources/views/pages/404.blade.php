@extends('layouts.minimal')

@section('title', 'Page Not Found')
@section('meta_description', 'The page you are looking for could not be found.')

@section('content')
<div class="mx-auto max-w-[1280px] px-4 py-20 text-center sm:px-6 lg:px-8">
    <p class="text-8xl font-bold text-accent/20">404</p>
    <h1 class="mt-4 text-3xl font-bold text-primary">Page not found</h1>
    <p class="mx-auto mt-4 max-w-md text-gray-600">
        Sorry, we couldn&rsquo;t find the page you&rsquo;re looking for. Try searching or head back to the catalog.
    </p>
    <div class="mx-auto mt-8 max-w-md">
        <x-search-bar />
    </div>
    <div class="mt-8 flex flex-wrap justify-center gap-4">
        <a href="{{ route('home') }}" class="inline-flex rounded-lg bg-accent px-6 py-3 font-semibold text-white hover:bg-accent-dark">Home</a>
        <a href="{{ route('products.index') }}" class="inline-flex rounded-lg border border-accent px-6 py-3 font-semibold text-accent hover:bg-accent-light">All Products</a>
    </div>
</div>
@endsection
