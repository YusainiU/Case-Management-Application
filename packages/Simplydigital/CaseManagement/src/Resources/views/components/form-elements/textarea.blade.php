
<textarea 
    {{ $attributes->merge([
        'class' => 'appearance-none text-black block w-full border border-gray-700 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500',
        'id' => $id ?? null,
        'name' => $name ?? null,
        'wire:model' => $model ?? null,
        'rows' => $rows ?? 4,
        'hint' => $hint ?? null,
    ]) }}
></textarea>


@if ($hint)
    <p class="text-xs text-gray-500">{{ $hint }}</p>
@endif


