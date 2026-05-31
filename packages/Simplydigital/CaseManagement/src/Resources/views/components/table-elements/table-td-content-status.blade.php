@props([
    'case',
])

<span>
    {{ $case->state?->label() }}
</span>