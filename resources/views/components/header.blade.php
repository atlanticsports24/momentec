@php
    $freeShippingMin = config('site.min_free_shipping', 150);
    $contactEmail = config('site.contact_email');
    $contactPhone = config('site.contact_phone');
    $searchAjaxUrl = route('search');
    $searchPageUrl = route('search');
@endphp

<header
    id="site-header"
    style="position:sticky;top:0;z-index:200;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.08);"
    x-data="{
        mobileMenuOpen: false,
        miniCartOpen: false,
        searchOpen: false,
        searchQuery: '',
        searchResults: [],
        searchLoading: false,
        searchDebounce: null,
        openMega: null,
        openBrandsMega: false,
        openMobileCategory: null,

        closeAll() {
            this.mobileMenuOpen = false;
            this.miniCartOpen = false;
            this.searchOpen = false;
            this.openMega = null;
            this.openBrandsMega = false;
        },

        toggleMobileCategory(id) {
            this.openMobileCategory = this.openMobileCategory === id ? null : id;
        },

        onSearchInput() {
            clearTimeout(this.searchDebounce);
            this.searchDebounce = setTimeout(() => this.fetchSearch(), 300);
        },

        async fetchSearch() {
            const q = this.searchQuery.trim();
            if (q.length < 2) {
                this.searchResults = [];
                this.searchOpen = false;
                return;
            }

            this.searchLoading = true;
            try {
                const response = await fetch(`{{ $searchAjaxUrl }}?q=${encodeURIComponent(q)}&ajax=1`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await response.json();
                this.searchResults = data.products ?? [];
                this.searchOpen = true;
            } catch (e) {
                this.searchResults = [];
            } finally {
                this.searchLoading = false;
            }
        },

        searchViewAllUrl() {
            return `{{ $searchPageUrl }}?q=${encodeURIComponent(this.searchQuery.trim())}`;
        }
    }"
    x-effect="document.body.style.overflow = mobileMenuOpen || miniCartOpen ? 'hidden' : ''"
    @keydown.escape.window="closeAll()"
>
    {{-- 7.1 Top bar (desktop only) --}}
    <div class="hidden border-b border-gray-100 lg:block" style="background:#fff;">
        <div class="mx-auto flex max-w-[1280px] items-center justify-between px-4 py-2 sm:px-6 lg:px-8" style="color:#6b7280;font-size:13px;font-weight:500;">
            <p style="color:#6b7280;font-size:13px;font-weight:500;">
                Free shipping on orders over ${{ number_format($freeShippingMin, 0) }}
            </p>
            <div class="flex items-center gap-6">
                @if($contactPhone)
                    <a
                        href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}"
                        style="color:#374151;font-size:13px;font-weight:500;"
                        class="transition hover:!text-[#4f46e5]"
                    >
                        {{ $contactPhone }}
                    </a>
                @endif
                @if($contactEmail)
                    <a
                        href="mailto:{{ $contactEmail }}"
                        style="color:#374151;font-size:13px;font-weight:500;"
                        class="transition hover:!text-[#4f46e5]"
                    >
                        {{ $contactEmail }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- 7.2 Main header --}}
    <div class="border-b border-gray-200 bg-white">
        <div class="mx-auto max-w-[1280px] px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center gap-4 lg:h-[72px] lg:gap-8">
                {{-- Mobile menu toggle --}}
                <button
                    type="button"
                    class="inline-flex shrink-0 items-center justify-center rounded-lg p-2 text-gray-700 hover:bg-gray-100 lg:hidden"
                    @click="mobileMenuOpen = true"
                    aria-label="Open menu"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                {{-- Logo --}}
                <a href="{{ route('home') }}" class="shrink-0 text-xl font-bold tracking-tight text-primary lg:text-2xl">
                    @if(file_exists(public_path('images/logo.png')))
                        <img src="{{ asset('images/logo.png') }}" alt="Momentec" class="h-8 w-auto lg:h-10">
                    @else
                        Momentec
                    @endif
                </a>

                {{-- Search (desktop center) --}}
                <div class="relative mx-auto hidden flex-1 lg:block" style="max-width:600px;" @click.outside="searchOpen = false">
                    <form action="{{ route('search') }}" method="GET" role="search" @submit="if (!searchQuery.trim()) $event.preventDefault()">
                        <label for="header-search" class="sr-only">Search products</label>
                        <div style="position:relative;flex:1;max-width:600px;">
                            <div style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;pointer-events:none;">
                                <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input
                                id="header-search"
                                type="search"
                                name="q"
                                x-model="searchQuery"
                                @input="onSearchInput()"
                                @focus="if (searchQuery.trim().length >= 2) searchOpen = true; $el.style.borderColor='#4f46e5'; $el.style.background='#fff'"
                                @blur="$el.style.borderColor='#e5e7eb'; $el.style.background='#f9fafb'"
                                placeholder="Search products, brands..."
                                autocomplete="off"
                                style="width:100%;padding:10px 16px 10px 42px;border:1.5px solid #e5e7eb;border-radius:12px;font-size:14px;color:#111827;background:#f9fafb;outline:none;"
                            >
                        </div>
                    </form>

                    {{-- Live search dropdown --}}
                    <div
                        x-show="searchOpen && searchQuery.trim().length >= 2"
                        x-cloak
                        x-transition
                        class="absolute top-full z-50 mt-2 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl"
                    >
                        <div x-show="searchLoading" class="p-4 text-center text-sm text-gray-500">Searching...</div>
                        <ul x-show="!searchLoading && searchResults.length" class="max-h-80 divide-y divide-gray-100 overflow-y-auto">
                            <template x-for="(product, index) in searchResults" :key="index">
                                <li>
                                    <a :href="product.url" class="flex items-center gap-3 p-3 transition hover:bg-accent-light">
                                        <img :src="product.image" :alt="product.name" class="h-12 w-12 shrink-0 rounded-lg border border-gray-100 object-cover" loading="lazy" width="48" height="48">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-primary" x-text="product.name"></p>
                                            <p class="truncate text-xs text-gray-500" x-text="product.brand"></p>
                                        </div>
                                    </a>
                                </li>
                            </template>
                        </ul>
                        <p x-show="!searchLoading && !searchResults.length" class="p-4 text-sm text-gray-500">No products found.</p>
                        <a
                            :href="searchViewAllUrl()"
                            class="block border-t border-gray-100 bg-gray-50 px-4 py-3 text-center text-sm font-semibold text-accent hover:bg-accent-light"
                        >
                            View all results for &ldquo;<span x-text="searchQuery.trim()"></span>&rdquo;
                        </a>
                    </div>
                </div>

                {{-- Icons --}}
                <div class="ml-auto flex shrink-0 items-center gap-1 sm:gap-2">
                    {{-- Mobile search toggle --}}
                    <button
                        type="button"
                        class="inline-flex rounded-lg p-2 text-gray-700 hover:bg-gray-100 lg:hidden"
                        @click="searchOpen = !searchOpen; $nextTick(() => $refs.mobileSearch?.focus())"
                        aria-label="Search"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>

                    <a href="{{ url('/admin') }}" class="group inline-flex rounded-lg p-2 hover:bg-gray-100" aria-label="Account" title="Account">
                        <svg class="transition group-hover:!text-[#4f46e5]" style="color:#374151;width:22px;height:22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </a>

                    <button type="button" class="group inline-flex rounded-lg p-2 hover:bg-gray-100" aria-label="Wishlist" title="Wishlist (coming soon)">
                        <svg class="transition group-hover:!text-[#4f46e5]" style="color:#374151;width:22px;height:22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </button>

                    <button
                        type="button"
                        class="group relative inline-flex rounded-lg p-2 hover:bg-gray-100"
                        @click="miniCartOpen = true"
                        aria-label="Open cart"
                    >
                        <svg class="transition group-hover:!text-[#4f46e5]" style="color:#374151;width:22px;height:22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full text-[10px] font-bold" style="background:#4f46e5;color:#fff;">0</span>
                    </button>
                </div>
            </div>

            {{-- Mobile search panel --}}
            <div
                x-show="searchOpen"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="border-t border-gray-100 pb-4 lg:hidden"
                @click.outside="if (window.innerWidth < 1024) searchOpen = false"
            >
                <form action="{{ route('search') }}" method="GET" role="search" class="relative">
                    <label for="header-search-mobile" class="sr-only">Search products</label>
                    <input
                        x-ref="mobileSearch"
                        id="header-search-mobile"
                        type="search"
                        name="q"
                        x-model="searchQuery"
                        @input="onSearchInput()"
                        placeholder="Search products, brands..."
                        autocomplete="off"
                        class="w-full rounded-lg border border-gray-300 py-2.5 pl-4 pr-4 text-sm focus:border-accent focus:ring-2 focus:ring-accent"
                    >
                    <div
                        x-show="searchQuery.trim().length >= 2"
                        class="absolute top-full z-50 mt-2 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl"
                    >
                        <ul x-show="searchResults.length" class="max-h-60 divide-y divide-gray-100 overflow-y-auto">
                            <template x-for="(product, index) in searchResults" :key="'m-' + index">
                                <li>
                                    <a :href="product.url" class="flex items-center gap-3 p-3 hover:bg-accent-light">
                                        <img :src="product.image" :alt="product.name" class="h-10 w-10 rounded object-cover" loading="lazy">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold" x-text="product.name"></p>
                                            <p class="truncate text-xs text-gray-500" x-text="product.brand"></p>
                                        </div>
                                    </a>
                                </li>
                            </template>
                        </ul>
                        <a :href="searchViewAllUrl()" class="block border-t px-4 py-2 text-center text-sm font-semibold text-accent">View all results</a>
                    </div>
                </form>
            </div>
        </div>

        @include('components.nav-mega-menu')
    </div>

    @include('components.mini-cart')
</header>
