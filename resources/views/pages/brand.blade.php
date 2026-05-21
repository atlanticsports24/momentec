@extends('layouts.app')

@section('title', $brand->name . ' Products')
@section('meta_description', 'Browse all ' . $brand->name . ' products available at Momentec.')

@section('schema_json')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Organization",
    "name": @json($brand->name),
    "url": "{{ route('brands.show', $brand) }}"
}
</script>
@endsection

@section('content')
<div class="mx-auto max-w-[1280px] px-4 py-8 sm:px-6 lg:px-8">
    <x-product-catalog
        :products="$products"
        :all-brands="$allBrands"
        :all-categories="$allCategories"
        :all-colors="$allColors"
        :all-sizes="$allSizes"
        :price-floor="$priceFloor"
        :price-ceiling="$priceCeiling"
        :form-action="route('brands.show', $brand)"
        :clear-filters-url="route('brands.show', $brand)"
        :empty-clear-url="route('brands.show', $brand)"
        :page-title="$brand->name"
        :hide-brand-filter="true"
        :locked-brand-slug="$brand->slug"
        :breadcrumb-items="[
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Brands', 'url' => route('brands.index')],
            ['label' => $brand->name],
        ]"
    >
        <x-slot:subtitle>
            {{ number_format($brand->products_count) }} {{ Str::plural('product', $brand->products_count) }} available
        </x-slot:subtitle>
    </x-product-catalog>
</div>
@endsection
