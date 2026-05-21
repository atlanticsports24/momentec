@extends('layouts.app')

@section('title', $category->name)
@section('meta_description', 'Shop ' . $category->name . ' sports apparel at Momentec.')

@section('schema_json')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BreadcrumbList",
    "itemListElement": [
        {
            "@@type": "ListItem",
            "position": 1,
            "name": "Home",
            "item": "{{ route('home') }}"
        },
        @if($category->parent)
        {
            "@@type": "ListItem",
            "position": 2,
            "name": @json($category->parent->name),
            "item": "{{ route('categories.show', $category->parent) }}"
        },
        @endif
        {
            "@@type": "ListItem",
            "position": {{ $category->parent ? 3 : 2 }},
            "name": @json($category->name),
            "item": "{{ route('categories.show', $category) }}"
        }
    ]
}
</script>
@endsection

@section('content')
@php
    $breadcrumbItems = [
        ['label' => 'Home', 'url' => route('home')],
    ];
    if ($category->parent) {
        $breadcrumbItems[] = ['label' => $category->parent->name, 'url' => route('categories.show', $category->parent)];
    } else {
        $breadcrumbItems[] = ['label' => 'Categories', 'url' => route('categories.index')];
    }
    $breadcrumbItems[] = ['label' => $category->name];
@endphp

<div class="mx-auto max-w-[1280px] px-4 py-8 sm:px-6 lg:px-8">
    <x-product-catalog
        :products="$products"
        :all-brands="$allBrands"
        :all-categories="$allCategories"
        :all-colors="$allColors"
        :all-sizes="$allSizes"
        :price-floor="$priceFloor"
        :price-ceiling="$priceCeiling"
        :form-action="route('categories.show', $category)"
        :clear-filters-url="route('categories.show', $category)"
        :empty-clear-url="route('categories.show', $category)"
        :page-title="$category->name"
        :breadcrumb-items="$breadcrumbItems"
    >
        <x-slot:subtitle>
            {{ number_format($category->products_count) }} {{ Str::plural('product', $category->products_count) }} in this category
        </x-slot:subtitle>

        @if($category->children->isNotEmpty())
            <x-slot:beforeGrid>
                <section class="mb-8">
                    <h2 class="text-lg font-semibold text-primary">Subcategories</h2>
                    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        @foreach($category->children as $child)
                            <a
                                href="{{ route('categories.show', $child) }}"
                                class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-accent hover:shadow-md"
                            >
                                <h3 class="font-semibold text-primary">{{ $child->name }}</h3>
                                <p class="mt-1 text-xs text-gray-500">{{ $child->products_count }} products</p>
                            </a>
                        @endforeach
                    </div>
                </section>
            </x-slot:beforeGrid>
        @endif
    </x-product-catalog>
</div>
@endsection
