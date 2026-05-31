@props([
    'case',
])

<span>
    {{ $case->slaTasks?->sla_flag_text }}
</span>