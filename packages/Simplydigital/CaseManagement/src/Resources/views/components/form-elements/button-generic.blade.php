@props([
    'action' => 'action',
    'label' => 'button',
    'bg' => 'bg-gray-100',
    'text' => 'text-gray-700',
    'hover' => 'bg-gray-200',
    'darkBg' => 'dark:bg-gray-800',
    'darkText' => 'dark:text-gray-200',
    'darkHover' => 'dark:hover:bg-gray-700',
    'class' => 'rounded-lg px-4 py-2 text-sm font-medium',
])

<div>
    <button
        @class([
            $bg,
            $text,
            "hover:$hover",
            $darkBg,
            $darkText,
            $darkHover,
            $class,
            'hover:cursor-pointer',
        ])
        wire:click="{{ $action }}"
    >   
        {{ $label }}
    </button>
</div>