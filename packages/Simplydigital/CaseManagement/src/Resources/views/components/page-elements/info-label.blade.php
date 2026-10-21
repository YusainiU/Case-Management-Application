@props([
    'enabled' => true,
    'label' => 'Enabled'
])

@php
    $enable = 'inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20';
    $disable = 'inline-flex items-center gap-1.5 rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-800 ring-1 ring-inset ring-slate-500/20';
@endphp

<span
    class="{{ $enabled ? $enable : $disable }}">
    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
    {{ $label }}
</span>
