@props([
    'value' => '',
    'large' => false,
    'live' => false,
])

@php
    $inputId = 'search-' . uniqid();
@endphp

<form action="{{ route('search') }}" method="GET" role="search" {{ $attributes }}>
    <label for="{{ $inputId }}" class="sr-only">Search products</label>
    <div
        class="relative"
        @if($live)
            x-data="{
                query: @js($value),
                results: [],
                open: false,
                loading: false,
                debounce: null,
                onInput() {
                    clearTimeout(this.debounce);
                    this.debounce = setTimeout(() => this.fetchResults(), 300);
                },
                async fetchResults() {
                    const q = this.query.trim();
                    if (q.length < 2) { this.results = []; this.open = false; return; }
                    this.loading = true;
                    try {
                        const res = await fetch(`{{ route('search') }}?q=${encodeURIComponent(q)}&ajax=1`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        this.results = data.products ?? [];
                        this.open = true;
                    } finally { this.loading = false; }
                }
            }"
            @click.outside="open = false"
        @endif
    >
        <input
            id="{{ $inputId }}"
            type="search"
            name="q"
            @if($live) x-model="query" @input="onInput()" @else value="{{ $value }}" @endif
            placeholder="Search products, brands..."
            autocomplete="off"
            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-accent focus:ring-2 focus:ring-accent {{ $large ? 'py-3 text-lg' : '' }}"
        >

        @if($live)
            <div x-show="open && query.trim().length >= 2" x-cloak class="absolute top-full z-50 mt-2 w-full rounded-xl border border-gray-200 bg-white shadow-xl">
                <ul class="max-h-60 divide-y overflow-y-auto">
                    <template x-for="(product, i) in results" :key="i">
                        <li>
                            <a :href="product.url" class="flex gap-3 p-3 hover:bg-accent-light">
                                <img :src="product.image" class="h-10 w-10 rounded object-cover" alt="">
                                <div>
                                    <p class="text-sm font-semibold" x-text="product.name"></p>
                                    <p class="text-xs text-gray-500" x-text="product.brand"></p>
                                </div>
                            </a>
                        </li>
                    </template>
                </ul>
                <a :href="`{{ route('search') }}?q=${encodeURIComponent(query.trim())}`" class="block border-t px-4 py-2 text-center text-sm font-semibold text-accent">View all results</a>
            </div>
        @endif
    </div>
</form>
