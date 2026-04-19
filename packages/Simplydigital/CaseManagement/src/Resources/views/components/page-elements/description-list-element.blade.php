@props([
    'label',
    'value',
    'navigateEnabled' => false,
    'navigateToComponent' => null,
])

<div class="flex flex-col py-3">
    <dt class="mb-1 text-body dark:text-gray-300">{{ $label }}</dt>
    <dd class="text-lg font-medium">
        @if($navigateEnabled && $navigateToComponent)
            <a href="#" wire:click="navigateTo('{{ $navigateToComponent }}')" class="cursor-pointer text-primary-600 hover:underline">
                {{ $value }}
            </a>
        @else
            {{ $value }}
        @endif
    </dd>
</div>
