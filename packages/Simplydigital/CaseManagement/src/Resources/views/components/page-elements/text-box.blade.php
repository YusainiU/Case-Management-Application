@props([
    'text',
    'textCss' => 'border    bg-gray-100 border-gray-100 text-gray-900',
])

<div 
    @class([
        "block rounded leading-tight focus:outline-none  px-3 py-4 appearance-none mt-2",
        $textCss,        
    ])
>
    {{ $text }}
</div>