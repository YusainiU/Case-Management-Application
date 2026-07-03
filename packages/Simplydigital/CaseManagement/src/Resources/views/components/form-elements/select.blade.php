@props([
    'disabled' => isset($disabled) && $disabled,
    'required' => false,
    'model' => $model,
    'name' => $name ?? null,
    'multiple' => $multiple ?? '',
    'placeholder' => $placeholder ?? 'Select an option',
    'options' => $options ?? [],
    'hint' => $hint ?? null,
    'notlive' => $notlive ?? false,
])

<div>
    <select
        @if($notlive)
            wire:model="{{ $model }}"
        @else
            wire:model.live="{{ $model }}"
        @endif
        id="{{ $name }}"
        name="{{ $name }}"
        {{  $multiple }}
        class="
            appearance-none 
            text-black 
            block 
            w-full 
            border 
            border-gray-700 
            rounded
            py-3 
            px-4 
            mb-3 
            leading-tight 
            focus:outline-none 
            focus:bg-white 
            focus:border-gray-500"
        @disabled($disabled)
    >
        <option value="">-- {{ $placeholder }} --</option>
        @foreach($options as $key => $value)
            <option value="{{ $key }}">{{ $value }}</option>
        @endforeach
    </select>
    @if ($hint ?? null)
        <p class="text-xs text-gray-500">{{ $hint }}</p>
    @endif    
</div>