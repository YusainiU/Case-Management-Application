
@props([
    'title' => 'Title',
    'content' => '0',
    'subTitle' => '',
    'action' => null,
    'icon' => 'squares-2x2',
    'accent' => 'indigo',
])

@php
    $themes = [
        'red' => [
            'border' => 'border-t-red-500',
            'icon' => 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
            'value' => 'text-red-600 dark:text-red-400',
        ],
        'amber' => [
            'border' => 'border-t-amber-500',
            'icon' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
            'value' => 'text-gray-950 dark:text-white',
        ],
        'blue' => [
            'border' => 'border-t-blue-500',
            'icon' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
            'value' => 'text-gray-950 dark:text-white',
        ],
        'violet' => [
            'border' => 'border-t-violet-500',
            'icon' => 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400',
            'value' => 'text-gray-950 dark:text-white',
        ],
        'emerald' => [
            'border' => 'border-t-emerald-500',
            'icon' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
            'value' => 'text-emerald-600 dark:text-emerald-400',
        ],
        'slate' => [
            'border' => 'border-t-gray-300 dark:border-t-gray-600',
            'icon' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
            'value' => 'text-gray-950 dark:text-white',
        ],
        'indigo' => [
            'border' => 'border-t-indigo-500',
            'icon' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
            'value' => 'text-gray-950 dark:text-white',
        ],
        'cyan' => [
            'border' => 'border-t-cyan-500',
            'icon' => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-500/10 dark:text-cyan-400',
            'value' => 'text-gray-950 dark:text-white',
        ],
    ];

    $theme = $themes[$accent] ?? $themes['indigo'];
@endphp

<div @class([
    'group h-full overflow-hidden rounded-xl border border-gray-200',
    'border-t-2 bg-white shadow-sm transition-all duration-200',
    'hover:border-gray-300 hover:shadow-md',
    'dark:border-gray-800 dark:bg-gray-900 dark:hover:border-gray-700',
    $theme['border'],
])>
    <div class="p-5">
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-300">
                {{ $title }}
            </h3>

            <div @class([
                'flex size-9 shrink-0 items-center justify-center rounded-lg',
                $theme['icon'],
            ])>
                <flux:icon :name="$icon" class="size-4" />
            </div>
        </div>

        <div class="mt-4">
            <div @class([
                'text-3xl font-semibold tracking-tight tabular-nums',
                $theme['value'],
            ])>
                @if ($action)
                    <x-casemanagement::page-elements.href
                        :action="$action"
                        :label="$content"
                    />
                @else
                    {{ $content }}
                @endif
            </div>

            <p class="mt-2 text-sm leading-5 text-gray-500 dark:text-gray-400">
                {{ $subTitle }}
            </p>
        </div>
    </div>
</div>
