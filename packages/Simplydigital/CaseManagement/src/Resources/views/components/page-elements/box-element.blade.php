@props([
    'status' => 1,
    'title' => 'Title Here',
    'label' => 'Label Name Here',
    'containerClass' => 'rounded-xl border p-4',
    'containerBorder' => 'border-red-100',
    'containerBg' => 'bg-red-50',
    'wrapperClass' => 'flex items-start justify-between',
    'titleClass' => 'text-sm font-medium',
    'titleText' => 'text-red-700',
    'headerClass' => 'mt-2 text-3xl font-bold',
    'headerText' => 'text-red-900',
    'subtitleClass' => 'mt-1 text-xs',
    'subTitleText' => 'text-red-600',
])

<div @class([$containerClass,$containerBorder,$containerBg,])>
    <div @class([$wrapperClass,])>
        <div>
            <p @class([$titleClass,$titleText,])>
                {{ $title }}
            </p>
            <h3 @class([$headerClass,$headerText,])>
                {{ $status }}
            </h3>
            <p @class([$subtitleClass,$subTitleText,])>
                {{ $label }}
            </p>
        </div>
    </div>
</div>
