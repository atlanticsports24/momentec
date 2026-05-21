<div
    x-show="miniCartOpen"
    x-cloak
    class="fixed inset-0 z-[70]"
    role="dialog"
    aria-modal="true"
    aria-label="Shopping cart"
>
    <div
        class="absolute inset-0 bg-black/40"
        @click="miniCartOpen = false"
        x-show="miniCartOpen"
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>

    <aside
        class="absolute right-0 top-0 flex h-full w-full max-w-md flex-col bg-white shadow-xl"
        x-show="miniCartOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        @click.stop
    >
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-4">
            <h2 class="text-lg font-semibold text-primary">Your Cart</h2>
            <button
                type="button"
                @click="miniCartOpen = false"
                class="rounded-lg p-2 text-gray-500 hover:bg-gray-100"
                aria-label="Close cart"
            >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="flex flex-1 flex-col items-center justify-center p-8 text-center">
            <svg class="h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <p class="mt-4 text-sm font-medium text-gray-700">Your cart is empty</p>
            <p class="mt-1 text-xs text-gray-500">Browse products and add items to get started.</p>
            <a href="{{ route('products.index') }}" class="mt-6 inline-flex rounded-lg bg-accent px-6 py-3 text-sm font-semibold text-white hover:bg-accent-dark">
                Shop All Products
            </a>
        </div>
    </aside>
</div>
