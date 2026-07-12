@if ($paginator->hasPages())
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-6 text-sm">
        <span class="text-on-surface-variant text-xs">
            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </span>
        <div class="flex items-center gap-2">
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1.5 rounded-lg bg-surface-container text-on-surface-variant/40 cursor-not-allowed">Prev</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface transition-colors">Prev</a>
            @endif
            <span class="px-3 py-1.5 rounded-lg bg-primary-container/20 text-primary font-bold">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface transition-colors">Next</a>
            @else
                <span class="px-3 py-1.5 rounded-lg bg-surface-container text-on-surface-variant/40 cursor-not-allowed">Next</span>
            @endif
        </div>
    </div>
@endif
