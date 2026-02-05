<div class="mb-4">
    <input
        {{ $attributes->merge([
            'class' => 'appearance-none text-black block w-full border border-gray-700 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500',
            'wire:model' => $model ?? null,
            'id' => $id ?? null,
            'type' => $type ?? 'text',
            'value' => $value ?? old($id ?? ''),
            'placeholder' => $placeholder ?? '',
            'required' => $required ?? false,
            'autofocus' => $autofocus ?? false,
        ])}}
    />
</div>