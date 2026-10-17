@props([
    'label' => 'View',
    'action',
])

<button type="button" wire:click="{{ $action }}"
    {{ $attributes->merge([
        'class' => '
            inline-flex 
            items-center 
            gap-1.5
            rounded-lg border 
            border-gray-200
            bg-white 
            px-3 py-2 text-xs
            font-medium 
            text-gray-700
            transition-colors 
            hover:bg-gray-50
            hover:text-indigo-600
            focus-visible:outline-2
            focus-visible:outline-offset-2
            focus-visible:outline-indigo-600
            dark:border-gray-700
            dark:bg-gray-900
            dark:text-gray-300
            dark:hover:bg-gray-800',
    ]) }}>
        <flux:icon name="eye" class="size-3.5" />
        {{ $label }}
</button>
