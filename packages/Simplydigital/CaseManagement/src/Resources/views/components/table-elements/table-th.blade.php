
@props([
    'field',
    'sortable' => false,
    'direction' => null,
    'px' => 'px-5',
    'py' => 'py-3.5',
    'border' => 'border-b border-slate-200',
    'whitespace' => 'whitespace-nowrap',
    'bgColor' => 'bg-slate-50',
    'text' => 'text-left text-xs font-semibold uppercase tracking-wider text-slate-500',
    'align' => 'text-left',
])

<th
    scope="col"
    @if ($sortable)
        wire:click="sortByField('{{ $field }}','{{ $direction }}')"
        aria-sort="{{ $direction === 'asc' ? 'ascending' : ($direction === 'desc' ? 'descending' : 'none') }}"
    @endif
    @class([
        'select-none',
        $px,
        $py,
        $border,
        $whitespace,
        $bgColor,
        $text,
        $align,
        'cursor-pointer transition-colors duration-150',
        'hover:bg-slate-100 hover:text-slate-800' => $sortable,
        'focus-within:bg-slate-100' => $sortable,
    ])
    @if ($sortable)
        tabindex="0"
        role="button"
        wire:key="table-heading-{{ $field }}"
    @endif
    {{ $attributes->except(['class']) }}
>
    <div class="inline-flex items-center gap-2">
        <span>{{ $slot }}</span>

        @if ($sortable)
            <span class="inline-flex shrink-0 items-center text-slate-400">
                @if ($direction === 'asc')
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-3.5 w-3.5 text-indigo-600"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-label="Sorted ascending"
                    >
                        <path fill-rule="evenodd" d="M14.77 12.79a.75.75 0 0 1-1.06-.02L10 8.83l-3.71 3.94a.75.75 0 1 1-1.1-1.02l4.25-4.5a.75.75 0 0 1 1.1 0l4.25 4.5a.75.75 0 0 1-.02 1.04Z" clip-rule="evenodd" />
                    </svg>
                @elseif ($direction === 'desc')
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-3.5 w-3.5 text-indigo-600"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-label="Sorted descending"
                    >
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.1 1.02l-4.25 4.5a.75.75 0 0 1-1.1 0l-4.25-4.5a.75.75 0 0 1 .02-1.04Z" clip-rule="evenodd" />
                    </svg>
                @else
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-3.5 w-3.5 opacity-50"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path d="M10 3.5a.75.75 0 0 1 .75.75v9.19l2.22-2.22a.75.75 0 1 1 1.06 1.06l-3.5 3.5a.75.75 0 0 1-1.06 0l-3.5-3.5a.75.75 0 1 1 1.06-1.06l2.22 2.22V4.25A.75.75 0 0 1 10 3.5Z" />
                    </svg>
                @endif
            </span>
        @endif
    </div>
</th>
