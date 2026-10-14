@props([
    'disabled' => false,
    'required' => false,
    'model' => null,
    'name' => null,
    'multiple' => false,
    'placeholder' => 'Select an option',
    'options' => [],
    'hint' => null,
    'notlive' => false,

    // Styling
    'borderColor' => 'border-gray-200 dark:border-gray-700',
    'borderColorDisabled' => 'border-gray-200 dark:border-gray-700',
    'textColor' => 'text-gray-900',
    'textColorDisabled' => 'text-gray-50',
    'class' => '',
])

@php
    $hasError = $model
        ? $errors->has($model)
        : ($name ? $errors->has($name) : false);

    $isMultiple = filter_var($multiple, FILTER_VALIDATE_BOOLEAN)
        || $multiple === 'multiple';
@endphp

<div class="w-full">
    <div class="relative">
        <select
            @if ($model)
                @if ($notlive)
                    wire:model="{{ $model }}"
                @else
                    wire:model.live="{{ $model }}"
                @endif
            @endif

            id="{{ $name }}"
            name="{{ $name }}"
            @if ($isMultiple)
                multiple
            @endif
            @required($required)
            @disabled($disabled)

            @class([
                'block w-full appearance-none rounded-xl border',
                'bg-gray-50 py-3 pl-3.5 pr-10 text-sm shadow-sm',
                'transition focus:outline-none focus:ring-2',
                'focus:ring-indigo-500/20',
                $textColor => !$disabled,
                $textColorDisabled => $disabled,
                $borderColor => !$disabled && !$hasError,
                $borderColorDisabled => $disabled,
                'border-red-300 focus:border-red-500 focus:ring-red-500/20 dark:border-red-500/60' => $hasError,
                'focus:border-indigo-500 dark:focus:border-indigo-400' => !$disabled && !$hasError,
                'cursor-not-allowed bg-gray-50' => $disabled,
                'dark:bg-gray-50' => !$disabled,
                'dark:focus:ring-indigo-400/20',
                'dark:text-gray-500' => $disabled,
                $class,
            ])
        >
            @unless ($isMultiple)
                <option value="">-- {{ $placeholder }} --</option>
            @endunless

            @foreach ($options as $key => $value)
                <option
                    value="{{ $key }}"
                    @selected(
                        !$isMultiple
                        && $model
                        // && (string) data_get($this, $model) === (string) $key
                    )
                >
                    {{ $value }}
                </option>
            @endforeach
        </select>

        @unless ($isMultiple)
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5">
                <flux:icon
                    name="chevron-down"
                    class="size-4 text-gray-400"
                />
            </div>
        @endunless
    </div>

    @if ($hint)
        <p class="mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">
            {{ $hint }}
        </p>
    @endif

    @if ($hasError)
        <p class="mt-2 flex items-start gap-1.5 text-sm text-red-600 dark:text-red-400">
            <flux:icon name="exclamation-circle" class="mt-0.5 size-4 shrink-0" />
            <span>{{ $errors->first($model ?: $name) }}</span>
        </p>
    @endif
</div>