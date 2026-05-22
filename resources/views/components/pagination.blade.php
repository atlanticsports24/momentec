@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-wrap items-center justify-center gap-2">
        @if ($paginator->onFirstPage())
            <span class="rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-400">Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-accent-light">Previous</a>
        @endif

        @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
            @if ($page == $paginator->currentPage())
                <span class="rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-white">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 transition hover:bg-accent-light">{{ $page }}</a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-accent-light">Next</a>
        @else
            <span class="rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-400">Next</span>
        @endif
    </nav>
@endif
