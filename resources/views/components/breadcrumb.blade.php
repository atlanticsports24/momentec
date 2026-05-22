@props([
    'items' => [],
])

@if(count($items))
    <nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'mb-6']) }}>
        <ol class="flex flex-wrap items-center gap-1.5 text-sm text-gray-500">
            @foreach($items as $index => $item)
                <li class="flex items-center gap-1.5">
                    @if($index > 0)
                        <svg class="h-4 w-4 shrink-0 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    @endif

                    @if($index === 0 && ($item['label'] ?? '') === 'Home')
                        <a href="{{ $item['url'] ?? route('home') }}" class="inline-flex items-center gap-1 transition hover:text-accent">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span class="sr-only sm:not-sr-only">Home</span>
                        </a>
                    @elseif(!empty($item['url']) && $index < count($items) - 1)
                        <a href="{{ $item['url'] }}" class="font-medium transition hover:text-accent">
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="font-semibold text-gray-900" aria-current="page">
                            {{ $item['label'] }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
