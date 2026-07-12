@props([
    'id' => null,
    'name' => null,
    'model' => null,
    'rows' => 4,
    'hint' => null,
    'disabled' => false,
    'borderColor' => "border-gray-700",
    'borderColorDisabled' => 'border-gray-400',
    'textColor' => 'text-black',
    'textColorDisabled' => 'text-gray-400',
    'class' => 'appearance-none block w-full border rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500',
])


<textarea 
    {{ $attributes->merge([        
        'id' => $id,
        'name' => $name,
        'rows' => $rows,
        'hint' => $hint,
        'disabled' => $disabled,
    ]) }}

    @class([
        $class,
        $borderColor => ! $disabled,
        $textColor => ! $disabled,
        $borderColorDisabled => $disabled,
        $textColorDisabled => $disabled,
    ])

    @if($model)
        wire:model = "{{ $model }}"
    @endif

></textarea>


@if ($hint)
    <p class="text-xs text-gray-500">{{ $hint }}</p>
@endif


