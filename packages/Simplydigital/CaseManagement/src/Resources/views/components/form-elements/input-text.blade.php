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
    'appearance' =>  'appearance-none',
    'border' => 'block border rounded leading-tight focus:outline-none',
    'textcolor' => 'text-gray-900',
    'textcolorDisabled' => 'text-gray-400',
    'bordercolor' => 'border-gray-700',
    'borderColorDisabled' => 'border-gray-400',
    'bordercolorfocus' => 'focus:border-gray-500',
    'py' => 'py-3',
    'px' => 'px-4',
    'mb' => 'mb-3',
    'bgcolor' => 'bg-gray-100',
    'bgfocus' => 'focus:bg-white',
    'width' => 'w-full',
    'webkit' => null,
    'hint' => null,
])



<div class="">
    <input

        @class([
            $appearance,
            $border,
            $textcolor => ! $disabled,  
            $textcolorDisabled =>  $disabled,
            $bordercolor => ! $disabled,
            $borderColorDisabled => $disabled,
            $bordercolorfocus,
            $py,
            $px,
            $mb,
            $bgcolor,
            $bgfocus,
            $width,
            ($webkit ? $webkit : null),
        ])

        {{ $attributes
            ->except(['model', 'xmodel'])
            ->merge([
            'id' => $id,
            'name' => $name,
            'type' => $type,
            'placeholder' => $placeholder,
            'required' => $required,
            'autofocus' => $autofocus,
            'maxlength' => $maxlength,
        ])}}

        @if(isset($model) && $model)
            wire:model.live="{{ $model }}"
        @endif

        @if(isset($value) && $value)
             value="$value"   
        @endif
        
        @if(isset($type) && $type == 'number')
            {{ isset($min) && $min ? "min='$min'" : null }}
            {{ isset($max) && $max ? "max='$max'" : null }}
            {{ isset($steps) && $steps ? "steps='$steps'" : null }}
        @endif

        {{ isset($pattern) && $pattern ? "pattern='$pattern'":null }}        
        {{ isset($xmodel) && $xmodel ? "x-model='$xmodel'" : '' }}
        {{ isset($inputmode) && $inputmode ? "inputmode='$inputmode'" : null }}

        @if(isset($disabled) && $disabled === true)
            disabled
        @endif

    />

    @if ($hint)
        <p class="text-xs text-gray-500">{{ $hint }}</p>
    @endif
    
</div>