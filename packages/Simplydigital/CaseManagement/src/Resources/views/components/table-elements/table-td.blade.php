@props([
    'textcolor' => 'text-gray-600 border-t border-gray-100',
    'px' => 'px-6',
    'py' => 'py-3',
    'whitespace' => 'whitespace-nowrap',
    'align' => 'text-left',
])

<td
    @class([
        $textcolor,
        $px,
        $py,
        $whitespace,
        $align,
    ])
    {{ $attributes }}
>
    {{ $slot }}
</td>