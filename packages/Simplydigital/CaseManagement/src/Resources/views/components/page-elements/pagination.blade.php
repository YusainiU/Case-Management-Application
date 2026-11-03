
@props(['paginator'])

@if ($paginator->hasPages())
    <nav
        role="navigation"
        aria-label="Pagination Navigation"
        class="flex flex-wrap items-center justify-center gap-2"
    >
        {{-- Previous page --}}
        @if ($paginator->onFirstPage())
            <span class="rounded bg-slate-100 px-3 py-2 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                Previous
            </span>
        @else
            <button
                type="button"
                wire:click="previousPage"
                wire:loading.attr="disabled"
                rel="prev"
                class="rounded bg-slate-200 px-3 py-2 text-slate-700 hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
            >
                Previous
            </button>
        @endif

        {{-- Page numbers --}}
        @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
            @if ($page == $paginator->currentPage())
                <span
                    aria-current="page"
                    class="rounded bg-slate-800 px-3 py-2 text-white dark:bg-indigo-500/20 dark:text-indigo-300"
                >
                    {{ $page }}
                </span>
            @else
                <button
                    type="button"
                    wire:click="gotoPage({{ $page }})"
                    wire:loading.attr="disabled"
                    class="rounded bg-slate-100 px-3 py-2 text-slate-700 hover:bg-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                >
                    {{ $page }}
                </button>
            @endif
        @endforeach

        {{-- Next page --}}
        @if ($paginator->hasMorePages())
            <button
                type="button"
                wire:click="nextPage"
                wire:loading.attr="disabled"
                rel="next"
                class="rounded bg-slate-200 px-3 py-2 text-slate-700 hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
            >
                Next
            </button>
        @else
            <span class="rounded bg-slate-100 px-3 py-2 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                Next
            </span>
        @endif
    </nav>
@endif
