@props([
    'name' => '',
])

<div
    {{ $attributes->merge([
        'class' => 'flex h-10 w-10 shrink-0 items-center justify-center
                    rounded-xl border border-indigo-100 bg-indigo-50
                    text-sm font-semibold text-indigo-700
                    dark:border-indigo-400/20 dark:bg-indigo-500/10 dark:text-indigo-300',
    ]) }}
    aria-hidden="true"
>
    {{ \Illuminate\Support\Str::upper(
        \Illuminate\Support\Str::substr(trim($name ?? ''), 0, 1)
    ) ?: '?' }}
</div>