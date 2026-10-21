@props([
    'label' => 'manage',
    'action',
])

<button type="button" wire:click="{{ $action }}"
    {{ $attributes->merge([
        'class' => 'inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg
                        border border-slate-200 bg-blue-200 px-3 py-2
                        text-xs font-semibold text-slate-700 shadow-sm
                        transition hover:border-indigo-300 hover:bg-indigo-50
                        hover:text-indigo-700 focus:outline-none
                        focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
    ]) }}>

    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
        stroke-width="1.8" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6h15M4.5 12h15M4.5 18h15" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 4.5v3M15 10.5v3M10 16.5v3" />
    </svg>

    {{ $label }}
</button>
