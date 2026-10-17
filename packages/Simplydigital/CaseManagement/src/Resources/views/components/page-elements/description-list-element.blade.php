@props([
    'label',
    'value',
    'navigateEnabled' => false,
    'navigateToComponent' => null,
])

<div {{ $attributes->merge([
    'class' => 'grid grid-cols-1 gap-1 py-3 sm:grid-cols-[minmax(7rem,0.85fr)_minmax(0,1.5fr)] sm:gap-4',
]) }}>
    <dt class="text-sm font-medium text-slate-500">
        {{ $label }}
    </dt>

    <dd class="min-w-0 break-words text-sm font-semibold text-slate-800">
        @if ($navigateEnabled && $navigateToComponent)
            <button
                type="button"
                wire:click="navigateTo('{{ $navigateToComponent }}')"
                class="text-left font-semibold text-indigo-600 transition-colors
                       hover:text-indigo-800 hover:underline
                       focus:outline-none focus-visible:rounded
                       focus-visible:ring-2 focus-visible:ring-indigo-500
                       focus-visible:ring-offset-2"
            >
                {{ $value }}
            </button>
        @else
            {{ $value }}
        @endif
    </dd>
</div>
