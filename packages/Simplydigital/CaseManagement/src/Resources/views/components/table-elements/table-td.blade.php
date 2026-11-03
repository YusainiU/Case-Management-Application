
@props([
    'textcolor' => 'text-slate-600 dark:text-slate-300',
    'px' => 'px-5',
    'py' => 'py-4',
    'whitespace' => 'whitespace-nowrap',
    'align' => 'text-left',
])

<td
    @class([
        'border-b border-slate-100 text-sm leading-5 dark:border-slate-700',
        'last:border-b-0',
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
