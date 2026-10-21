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
                    focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2'
    ]) }}>
        <flux:icon name="eye" class="size-3.5" />
        {{ $label }}
</button>
