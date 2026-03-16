@props(['field', 'sortable' => false])

@php
    $th = "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider";
@endphp

<th 
    @if($sortable)
        wire:click="sortByField('{{ $field }}','{{ $direction ?? null }}')"
        class="{{ $th }} cursor-pointer"
    @endif
    class="{{ $th }}"
>
    {{ $slot }}
</th>