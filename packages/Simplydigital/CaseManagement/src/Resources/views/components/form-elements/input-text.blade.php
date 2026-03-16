<div class="">
    <input
        {{ $attributes->merge([
            'class' => 'appearance-none text-black block w-full border border-gray-700 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500',
            'wire:model.live' => $model ?? null,
            'id' => $id ?? null,
            'name' => $name ?? null,
            'type' => $type ?? 'text',
            'value' => $value ?? null,
            'placeholder' => $placeholder ?? '',
            'required' => $required ?? false,
            'autofocus' => $autofocus ?? false,
            'disabled' => $disabled ?? false,
            // 'x-model' => $xmodel ?? '',
        ])}}
        {{ isset($xmodel) ? "x-model='$xmodel'" : '' }}
    />
    @if ($hint ?? null)
        <p class="text-xs text-gray-500">{{ $hint }}</p>
    @endif    
</div>