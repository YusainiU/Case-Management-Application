@props([
    'label' => 'Edit',
    'action',
])

<button
    type="button"
    wire:click="{{ $action }}"
    {{ $attributes->merge([
        'class' => 'inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg
                    border border-slate-200 bg-white px-3 py-2
                    text-xs font-semibold text-slate-700 shadow-sm
                    transition hover:border-indigo-300 hover:bg-indigo-50
                    hover:text-indigo-700 focus:outline-none
                    focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
    ]) }}
>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-3.5 w-3.5"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        stroke-width="1.8"
        aria-hidden="true"
    >
        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"
        />
        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M19.5 7.125L16.875 4.5"
        />
    </svg>

    {{ $label }}
</button>
