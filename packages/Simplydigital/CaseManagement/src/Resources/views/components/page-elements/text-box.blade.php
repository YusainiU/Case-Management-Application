@props([
    'text',
    'textCss' => 'border bg-gray-100 border-gray-100 text-gray-900 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100',
])

<div 
    @class([
        "mt-2 block appearance-none rounded px-3 py-4 leading-tight focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100",
        $textCss,        
    ])
>
    {{ $text }}
</div>