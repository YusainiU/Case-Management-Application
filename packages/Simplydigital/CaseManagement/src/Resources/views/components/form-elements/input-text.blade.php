<div class="">
    <input

        @class([
            $appearance ?? 'appearance-none',
            $border ?? 'block border rounded leading-tight focus:outline-none',
            $textcolor ?? 'text-gray-900',
            $bordercolor ?? 'border-gray-700',
            $bordercolorfocus ?? 'focus:border-gray-500',
            $py ?? 'py-3',
            $px ?? 'px-4',
            $mb ?? 'mb-3',
            $bgcolor ?? 'bg-gray-100',
            $bgfocus ?? 'focus:bg-white',
            $width ?? 'w-full',
        ])

        {{ $attributes
            ->except(['model',])
            ->merge([
            'id' => $id ?? null,
            'name' => $name ?? null,
            'type' => $type ?? 'text',
            'placeholder' => $placeholder ?? '',
            'required' => $required ?? false,
            'autofocus' => $autofocus ?? false,
            'disabled' => $disabled ?? false,
            'maxlength' => $maxlength ?? null,
        ])}}

        @if(isset($model))
            wire:model.live="{{ $model }}"
        @endif

        @if(isset($value))
             value="$value"   
        @endif
        
        @if(isset($type) && $type == 'number')
            {{ isset($min) ? "min='$min'" : null }}
            {{ isset($max) ? "max='$max'" : null }}
            {{ isset($steps) ? "steps='$steps'" : null }}
        @endif

        {{ isset($pattern) ? "pattern='$pattern'":null }}        
        {{ isset($xmodel) ? "x-model='$xmodel'" : '' }}
        {{ isset($inputmode) ? "inputmode='$inputmode'" : null }}

        @if(isset($disabled))
            disabled
        @endif

    />

    @if ($hint ?? null)
        <p class="text-xs text-gray-500">{{ $hint }}</p>
    @endif
    
</div>