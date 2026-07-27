@props([
    'action' => 'action',
    'label' => 'button',
    'bg' => 'bg-gray-100',
    'text' => 'text-gray-700',
    'hover' => 'bg-gray-200',
    'class' => 'rounded-lg px-4 py-2 text-sm font-medium',
])

<div>
    <button
        @class([
            $bg,
            $text,
            "hover:$hover",
            $class,
        ])
        wire:click="{{ $action }}"
    >
        {{ $label }}
    </button>
</div>