<td 
    @class([
        $textcolor ?? 'text-gray-600',
        $px ?? 'px-6',
        $py ?? 'py-3',
        $border ?? 'border-t border-gray-100',
        $whitespace ?? 'whitespace-nowrap',
        $align ?? 'text-left',
    ])
    {{ $attributes->except(['align', 'class']) }}
>
    {{ $slot }}
</td>