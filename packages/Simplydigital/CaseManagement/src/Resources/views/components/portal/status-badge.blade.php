@props(['state'])

@php
    $state = $state instanceof \Simplydigital\CaseManagement\Enums\CaseState
        ? $state
        : \Simplydigital\CaseManagement\Enums\CaseState::from($state);

    $classes = match ($state) {
        \Simplydigital\CaseManagement\Enums\CaseState::Completed,
        \Simplydigital\CaseManagement\Enums\CaseState::Resolved,
        \Simplydigital\CaseManagement\Enums\CaseState::Closed => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/20',
        \Simplydigital\CaseManagement\Enums\CaseState::Cancelled => 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-400/10 dark:text-rose-300 dark:ring-rose-400/20',
        \Simplydigital\CaseManagement\Enums\CaseState::Paused => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/20',
        default => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20 dark:bg-indigo-400/10 dark:text-indigo-300 dark:ring-indigo-400/20',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset '.$classes]) }}>
    {{ $state->label() }}
</span>
