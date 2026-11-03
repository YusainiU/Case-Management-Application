@props([
    'id' => null,
    'name' => null,
    'model' => null,
    'rows' => 4,
    'hint' => null,
    'disabled' => false,
    'borderColor' => 'border-gray-200 dark:border-gray-700',
    'borderColorDisabled' => 'border-gray-200 dark:border-gray-700',
    'textColor' => 'text-gray-900 dark:text-gray-100',
    'textColorDisabled' => 'text-gray-500 dark:text-gray-400',
    'class' => '',
])

@php
    $field = $model ?: $name;
    $hasError = $field ? $errors->has($field) : false;
@endphp

<div class="w-full">
    <textarea
        {{ $attributes->except(['hint'])->merge([
            'id' => $id ?: $name,
            'name' => $name,
            'rows' => $rows,
        ]) }}
        @if ($model)
            wire:model.live="{{ $model }}"
        @endif
        @disabled($disabled)
        @class([
            'block w-full rounded-xl border bg-gray-50 px-3.5 py-3 dark:bg-gray-800',
            'text-sm leading-6 text-gray-900 placeholder:text-gray-400 dark:text-gray-100 dark:placeholder:text-gray-500',
            'shadow-sm transition duration-150 resize-y',
            'focus:outline-none focus:ring-2',
            'focus:ring-indigo-500/20',
            $textColor => !$disabled && !$hasError,
            $textColorDisabled => $disabled,
            $borderColor => !$disabled && !$hasError,
            $borderColorDisabled => $disabled,
            'border-red-300 focus:border-red-500 focus:ring-red-500/20 dark:border-red-500/60' => $hasError,
            'focus:border-indigo-500 dark:focus:border-indigo-400' => !$disabled && !$hasError,
            'cursor-not-allowed bg-gray-100 dark:bg-gray-900' => $disabled,
            $class,
        ])
        @if ($hasError)
            aria-invalid="true"
            aria-describedby="{{ ($id ?: $name) . '-error' }}"
        @endif
    ></textarea>

    @if ($hint)
        <p class="mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">
            {{ $hint }}
        </p>
    @endif

    @if ($hasError)
        <p
            id="{{ ($id ?: $name) . '-error' }}"
            class="mt-2 flex items-start gap-1.5 text-sm text-red-600 dark:text-red-400"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="mt-0.5 size-4 shrink-0"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
            >
                <path
                    fill-rule="evenodd"
                    d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9 5.75a1 1 0 1 1 2 0v4a1 1 0 0 1-2 0v-4Zm1 7a1.25 1.25 0 1 0 0 2.5 1.25 1.25 0 0 0 0-2.5Z"
                    clip-rule="evenodd"
                />
            </svg>

            <span>{{ $errors->first($field) }}</span>
        </p>
    @endif
</div>
