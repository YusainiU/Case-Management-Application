@props([
    'bgcolour' => "bg-gray-900",
    'textcolour' => "text-white",
    'znumber' => 'z-1000',
    'text' => 'tooltip here', 
])

<div class="relative inline-block group">

    {{ $slot }}

    <div
        @class([
            'absolute left-1/2 top-full mt-2 -translate-x-1/2 whitespace-nowrap rounded-md px-2 py-1', 
            'text-xs opacity-0 transition-opacity duration-200 group-hover:opacity-100 pointer-events-none',
            $bgcolour,
            $textcolour,
            $znumber,    
        ])
    >
        {{ $text }}
    </div>

</div>