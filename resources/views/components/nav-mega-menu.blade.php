{{-- 7.3 Desktop navigation + mega menus --}}
<nav id="desktop-main-nav" class="hidden border-t border-gray-100 lg:block" aria-label="Main navigation">
    <div class="mx-auto max-w-[1280px] px-4 sm:px-6 lg:px-8">
        <ul class="flex flex-wrap items-center gap-1">
            <li>
                <a href="{{ route('home') }}" class="block px-3 py-3 text-sm font-medium text-gray-700 transition hover:text-accent">Home</a>
            </li>
            <li>
                <a href="{{ route('products.index') }}" class="block px-3 py-3 text-sm font-medium text-gray-700 transition hover:text-accent">All Products</a>
            </li>

            {{-- Brands mega menu --}}
            <li
                class="relative"
                @mouseenter="openBrandsMega = true; openMega = null"
            >
                <a
                    href="{{ route('brands.index') }}"
                    class="flex items-center gap-1 px-3 py-3 text-sm font-medium text-gray-700 transition hover:text-accent"
                    :class="openBrandsMega ? 'text-accent' : ''"
                >
                    Brands
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </a>
                <div
                    x-show="openBrandsMega"
                    x-cloak
                    x-transition
                    style="position:fixed;left:0;right:0;width:100vw;top:var(--nav-bottom,140px);z-index:9999;background:#fff;border-top:3px solid #4f46e5;box-shadow:0 20px 60px rgba(0,0,0,.12);"
                    @mouseenter="openBrandsMega = true; openMega = null"
                    @mouseleave="openBrandsMega = false"
                >
                    <div style="max-width:1280px;margin:0 auto;padding:28px 32px;">
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                        @forelse($navBrands as $brand)
                            <a
                                href="{{ route('brands.show', $brand) }}"
                                class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-accent-light hover:text-accent"
                            >
                                {{ $brand->name }}
                            </a>
                        @empty
                            <p class="col-span-full text-sm text-gray-500">No brands available.</p>
                        @endforelse
                    </div>
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <a href="{{ route('brands.index') }}" class="text-sm font-semibold text-accent hover:underline">View all brands &rarr;</a>
                    </div>
                    </div>
                </div>
            </li>

            <li>
                <a href="{{ route('categories.index') }}" class="block px-3 py-3 text-sm font-medium text-gray-700 transition hover:text-accent">Categories</a>
            </li>

            {{-- Dynamic category mega menus --}}
            @foreach($navCategories as $category)
                <li
                    class="relative"
                    @mouseenter="openMega = {{ $category->id }}; openBrandsMega = false"
                >
                    <a
                        href="{{ route('categories.show', $category) }}"
                        class="flex items-center gap-1 px-3 py-3 text-sm font-medium text-gray-700 transition hover:text-accent"
                        :class="openMega === {{ $category->id }} ? 'text-accent' : ''"
                    >
                        {{ $category->name }}
                        @if($category->children->count())
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        @endif
                    </a>

                    @if($category->children->count())
                        <div
                            x-show="openMega === {{ $category->id }}"
                            x-cloak
                            x-transition
                            style="position:fixed;left:0;right:0;width:100vw;top:var(--nav-bottom,140px);z-index:9999;background:#fff;border-top:3px solid #4f46e5;box-shadow:0 20px 60px rgba(0,0,0,.12);"
                            @mouseenter="openMega = {{ $category->id }}; openBrandsMega = false"
                            @mouseleave="openMega = null"
                        >
                            <div style="max-width:1280px;margin:0 auto;padding:28px 32px;display:grid;grid-template-columns:200px repeat(4,1fr);gap:24px;">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Category</p>
                                    <a
                                        href="{{ route('categories.show', $category) }}"
                                        class="mt-1 block text-lg font-bold text-primary hover:text-accent"
                                    >
                                        {{ $category->name }}
                                    </a>
                                    <a
                                        href="{{ route('categories.show', $category) }}"
                                        class="mt-4 inline-block text-sm font-semibold text-accent hover:underline"
                                    >
                                        Shop all {{ $category->name }} &rarr;
                                    </a>
                                </div>

                                @foreach($category->children->take(4) as $subcategory)
                                    <div>
                                        <a
                                            href="{{ route('categories.show', $subcategory) }}"
                                            class="text-sm font-semibold text-primary hover:text-accent"
                                        >
                                            {{ $subcategory->name }}
                                        </a>
                                        @if($subcategory->children->count())
                                            <ul class="mt-3 space-y-2">
                                                @foreach($subcategory->children as $child)
                                                    <li>
                                                        <a
                                                            href="{{ route('categories.show', $child) }}"
                                                            class="text-sm text-gray-600 transition hover:text-accent"
                                                        >
                                                            {{ $child->name }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</nav>

{{-- Mobile slide-in drawer --}}
<div
    x-show="mobileMenuOpen"
    x-cloak
    class="fixed inset-0 z-[60] lg:hidden"
    role="dialog"
    aria-modal="true"
    aria-label="Mobile navigation"
>
    <div
        class="absolute inset-0 bg-black/50"
        @click="mobileMenuOpen = false"
        x-show="mobileMenuOpen"
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>

    <aside
        class="absolute left-0 top-0 flex h-full w-full max-w-sm flex-col bg-white shadow-xl"
        x-show="mobileMenuOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        @click.stop
    >
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-4">
            <span class="text-lg font-bold text-primary">Menu</span>
            <button
                type="button"
                class="rounded-lg p-2 text-gray-500 hover:bg-gray-100"
                @click="mobileMenuOpen = false"
                aria-label="Close menu"
            >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-4">
            <ul class="space-y-1 border-b border-gray-100 pb-4">
                <li><a href="{{ route('home') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-gray-800 hover:bg-accent-light">Home</a></li>
                <li><a href="{{ route('products.index') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-gray-800 hover:bg-accent-light">All Products</a></li>
                <li><a href="{{ route('brands.index') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-gray-800 hover:bg-accent-light">Brands</a></li>
                <li><a href="{{ route('categories.index') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-gray-800 hover:bg-accent-light">Categories</a></li>
            </ul>

            @if($navBrands->count())
                <p class="mt-4 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Top Brands</p>
                <ul class="mt-2 space-y-1">
                    @foreach($navBrands->take(12) as $brand)
                        <li>
                            <a href="{{ route('brands.show', $brand) }}" class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-accent-light">
                                {{ $brand->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif

            <p class="mt-6 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Shop by Category</p>
            <ul class="mt-2 space-y-1">
                @foreach($navCategories as $category)
                    <li class="rounded-lg border border-gray-100">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between px-3 py-2.5 text-left text-sm font-semibold text-primary"
                            @click="toggleMobileCategory({{ $category->id }})"
                            :aria-expanded="openMobileCategory === {{ $category->id }}"
                        >
                            {{ $category->name }}
                            <svg
                                class="h-4 w-4 shrink-0 text-gray-500 transition"
                                :class="openMobileCategory === {{ $category->id }} ? 'rotate-180' : ''"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div
                            x-show="openMobileCategory === {{ $category->id }}"
                            x-cloak
                            x-transition
                            class="border-t border-gray-100 bg-gray-50 px-3 py-2"
                        >
                            <a href="{{ route('categories.show', $category) }}" class="block py-2 text-sm font-medium text-accent">All {{ $category->name }}</a>
                            @foreach($category->children as $subcategory)
                                <div class="py-2">
                                    <a href="{{ route('categories.show', $subcategory) }}" class="text-sm font-medium text-gray-800 hover:text-accent">
                                        {{ $subcategory->name }}
                                    </a>
                                    @if($subcategory->children->count())
                                        <ul class="mt-1 space-y-1 pl-3">
                                            @foreach($subcategory->children as $child)
                                                <li>
                                                    <a href="{{ route('categories.show', $child) }}" class="text-sm text-gray-600 hover:text-accent">
                                                        {{ $child->name }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="border-t border-gray-200 p-4 text-xs text-gray-500">
            @if($contactPhone = config('site.contact_phone'))
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}" class="block hover:text-accent">{{ $contactPhone }}</a>
            @endif
            @if($contactEmail = config('site.contact_email'))
                <a href="mailto:{{ $contactEmail }}" class="mt-1 block hover:text-accent">{{ $contactEmail }}</a>
            @endif
        </div>
    </aside>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const nav = document.querySelector('#desktop-main-nav');
    if (nav) {
        const update = () => document.documentElement.style.setProperty('--nav-bottom', nav.getBoundingClientRect().bottom + 'px');
        update();
        window.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
    }
});
</script>
@endpush
