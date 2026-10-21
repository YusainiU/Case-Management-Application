
@props([
    'textColor' => 'text-slate-700',
    'mt' => 'mt-5',
    'mb' => 'mb-5',
    'bgColor' => 'bg-white',
    'maxHeight' => null,
    'px' => 'px-0',
    'py' => 'py-0',
    'border' => 'border border-slate-200',
    'whitespace' => 'whitespace-nowrap',
    'align' => 'text-left',
    'stickyHeader' => false,
])

<div
    @class([
        'relative w-full overflow-x-auto rounded-xl',
        'shadow-sm ring-1 ring-slate-900/5',
        $textColor,
        $mt,
        $mb,
        $bgColor,
        $border,
        $px,
        $py,
    ])
    {{ $attributes->except(['class']) }}
>
    <div
        @class([
            'w-full overflow-x-auto',
            'overflow-y-auto' => $maxHeight,
            $maxHeight,
        ])
    >
        <table
            @class([
                'w-full min-w-full border-collapse',
                $whitespace,
                $align,
            ])
        >
            @if ($stickyHeader)
                <style>
                    .table-sticky-head thead {
                        position: sticky;
                        top: 0;
                        z-index: 10;
                    }
                </style>
            @endif

            {{ $slot }}
        </table>
    </div>
</div>
