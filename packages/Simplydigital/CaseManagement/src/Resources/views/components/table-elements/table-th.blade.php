@props(['field', 'sortable' => false])

<th 
    @if($sortable)
        wire:click="sortByField('{{ $field }}','{{ $direction ?? null }}')"
    @endif
    @class([
        $px ?? 'px-6',
        $py ?? 'py-3',
        $border ?? 'border-t border-gray-100',
        $whitespace ?? 'whitespace-nowrap',
        $mb ?? 'mb-3',
        $bgColor ?? 'bg-zinc-100',
        $text ?? 'text-left text-xs font-medium text-gray-500 uppercase tracking-wider',
        'cursor-pointer' => $sortable,
    ])
    {{ $attributes->except(['class']) }}
>
    {{ $slot }}
</th>