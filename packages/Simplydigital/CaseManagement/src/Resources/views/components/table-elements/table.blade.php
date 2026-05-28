<div 
    @class([
        "overflow-x-auto rounded-lg shadow overflow-y-auto relative",
        $textColor ?? 'text-gray-700',
        $mt ?? 'mt-5',
        $mb ?? 'mb-5',
        $bgColor ?? 'bg-white',
        $maxHeight ?? 'max-h-96',
        $px ?? 'px-4',
        $py ?? 'py-4',
        $border ?? 'border border-gray-200',
        $whitespace ?? 'whitespace-nowrap',
        $align ?? 'text-left',
    ])
>
    <table class="w-full">
        {{ $slot }}
    </table>
</div