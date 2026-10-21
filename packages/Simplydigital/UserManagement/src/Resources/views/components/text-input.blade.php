@props([
    'id' => null,
    'name' => null,
    'type' => 'text',
    'placeholder' => '',
    'required' => false,
    'autofocus' => false,
    'disabled' => false,
    'maxlength' => null,
    'min' => null,
    'max' => null,
    'steps' => null,
    'pattern' => null,
    'inputmode' => null,
    'model' => null,
    'xmodel' => null,
    'value' => null,

    // Styling
    'appearance' => 'appearance-none',
    'border' => 'block rounded-xl border shadow-sm transition',
    'textcolor' => 'text-gray-900',
    'textcolorDisabled' => 'text-gray-500 dark:text-gray-400',
    'bordercolor' => 'border-gray-200 dark:border-gray-700',
    'borderColorDisabled' => 'border-gray-200 dark:border-gray-700',
    'bordercolorfocus' => 'focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:focus:border-indigo-400',
    'py' => 'py-3',
    'px' => 'px-3.5',
    'mb' => 'mb-0',
    'bgcolor' => 'bg-gray-50',
    'bgfocus' => 'focus:bg-gray-200',
    'width' => 'w-full',
    'webkit' => null,

    // Supporting text
    'hint' => null,
    'hint_important' => null,
])

@php
    $hasError = $model
        ? $errors->has($model)
        : ($name ? $errors->has($name) : false);
@endphp

<div class="w-full">
    <input
        @class([
            $appearance,
            $border,
            $textcolor => !$disabled,
            $textcolorDisabled => $disabled,
            $bordercolor => !$disabled && !$hasError,
            $borderColorDisabled => $disabled,
            'border-red-300 focus:border-red-500 focus:ring-red-500/20 dark:border-red-500/60' => $hasError,
            $bordercolorfocus => !$disabled && !$hasError,
            $py,
            $px,
            $mb,
            $bgcolor => !$disabled,
            'cursor-not-allowed bg-gray-100' => $disabled,
            $bgfocus => !$disabled,
            $width,
            'placeholder:text-gray-400 dark:placeholder:text-gray-500',
            'focus:outline-none',
            'disabled:cursor-not-allowed disabled:opacity-100',
            $webkit,
        ])
        {{
            $attributes
                ->except(['model', 'xmodel', 'value'])
                ->merge([
                    'id' => $id,
                    'name' => $name,
                    'type' => $type,
                    'placeholder' => $placeholder,
                    'required' => $required,
                    'autofocus' => $autofocus,
                    'maxlength' => $maxlength,
                    'min' => $min,
                    'max' => $max,
                    'step' => $steps,
                    'pattern' => $pattern,
                    'inputmode' => $inputmode,
                    'disabled' => $disabled,
                    'value' => $value,
                ])
        }}

        @if ($model)
            wire:model.live="{{ $model }}"
        @endif

        @if ($xmodel)
            x-model="{{ $xmodel }}"
        @endif

        @if (isset($value))
            value="{{ $value }}"
        @endif
    />

    @if ($hint)
        <p @class([
            'mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400',
            $hint_important,
        ])>
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
