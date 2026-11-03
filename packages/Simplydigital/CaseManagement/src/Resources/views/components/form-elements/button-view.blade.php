@props([
    'label' => 'View',
    'action',
])

<button type="button" wire:click="{{ $action }}"
    {{ $attributes->merge([
        'class' => 'inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg
                    border border-slate-200 bg-white px-3 py-2
                    text-xs font-semibold text-slate-700 shadow-sm
                    transition hover:border-indigo-300 hover:bg-indigo-50
                    hover:text-indigo-700 focus:outline-none
                    focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                    dark:border-slate-700 dark:bg-gray-800 dark:text-slate-200
                    dark:hover:border-indigo-400/40 dark:hover:bg-indigo-500/10
                    dark:hover:text-indigo-300 dark:focus:ring-offset-gray-900'
    ]) }}>
        <flux:icon name="eye" class="size-3.5" />
        {{ $label }}
</button>
