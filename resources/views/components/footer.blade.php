@php
    $footerBrands = $navBrands->take(6);
    $contactEmail = config('site.contact_email');
    $contactPhone = config('site.contact_phone');
    $contactAddress = config('site.address');
@endphp

<footer class="bg-primary text-gray-300">
    <div class="mx-auto max-w-[1280px] px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4 lg:gap-8">
            {{-- Col 1: Logo + description --}}
            <div class="sm:col-span-2 lg:col-span-1">
                <a href="{{ route('home') }}" class="text-2xl font-bold text-white">Momentec</a>
                <p class="mt-4 max-w-xs text-sm leading-relaxed text-gray-400">
                    Your B2B sports apparel catalog. Browse products by brand, category, color, and size — built for teams and retailers.
                </p>
            </div>

            {{-- Col 2: Quick links --}}
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wide text-white">Quick Links</h2>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('home') }}" class="transition hover:text-white">Home</a></li>
                    <li><a href="{{ route('products.index') }}" class="transition hover:text-white">All Products</a></li>
                    <li><a href="{{ route('brands.index') }}" class="transition hover:text-white">Brands</a></li>
                    <li><a href="{{ route('categories.index') }}" class="transition hover:text-white">Categories</a></li>
                    <li><a href="{{ route('search') }}" class="transition hover:text-white">Search</a></li>
                </ul>
            </div>

            {{-- Col 3: Top brands --}}
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wide text-white">Top Brands</h2>
                <ul class="mt-4 space-y-2.5 text-sm">
                    @forelse($footerBrands as $brand)
                        <li>
                            <a href="{{ route('brands.show', $brand) }}" class="transition hover:text-white">
                                {{ $brand->name ?? $brand }}
                            </a>
                        </li>
                    @empty
                        <li class="text-gray-500">No brands yet.</li>
                    @endforelse
                    @if($navBrands->count() > 6)
                        <li>
                            <a href="{{ route('brands.index') }}" class="font-medium text-accent-light transition hover:text-white">
                                View all brands &rarr;
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            {{-- Col 4: Contact --}}
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wide text-white">Contact</h2>
                <ul class="mt-4 space-y-3 text-sm">
                    @if($contactAddress)
                        <li class="flex gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-gray-400">{{ $contactAddress }}</span>
                        </li>
                    @endif
                    @if($contactEmail)
                        <li class="flex gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <a href="mailto:{{ $contactEmail }}" class="transition hover:text-white">{{ $contactEmail }}</a>
                        </li>
                    @endif
                    @if($contactPhone)
                        <li class="flex gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}" class="transition hover:text-white">{{ $contactPhone }}</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-primary-900/50 pt-8 text-xs text-gray-500 sm:flex-row">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Momentec') }}. All rights reserved.</p>
            <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-6">
                <span>Built with Laravel</span>
                <button
                    type="button"
                    onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
                    class="font-medium text-gray-400 transition hover:text-white"
                >
                    Back to top
                </button>
            </div>
        </div>
    </div>
</footer>
