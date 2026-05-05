<div class="">
    <input
    
        @class([
            'appearance-none text-black block border border-gray-700 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500',
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
    />
    @if ($hint ?? null)
        <p class="text-xs text-gray-500">{{ $hint }}</p>
    @endif    
</div>