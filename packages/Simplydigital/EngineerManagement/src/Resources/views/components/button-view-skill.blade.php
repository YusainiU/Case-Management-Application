@props([
    'label' => 'View Skills',
    'action',
])

<button type="button" wire:click="{{ $action }}"
    class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-1">
    <x-engineermanagement-ui-icon name="skill" class="h-3.5 w-3.5 mr-2" />
    {{ $label }}
</button>
