@props([
    'status' => 1,
    'title' => 'Title Here',
    'label' => 'Label Name Here',
    'containerClass' => 'rounded-xl border p-4',
    'containerBorder' => 'border-red-100',
    'containerBg' => 'bg-red-50',
    'containerBorderDark' => 'dark:border-red-400/20',
    'containerBgDark' => 'dark:bg-red-400/10',
    'wrapperClass' => 'flex items-start justify-between',
    'titleClass' => 'text-sm font-medium',
    'titleText' => 'text-red-700',
    'titleTextDark' => 'dark:text-red-300',
    'headerClass' => 'mt-2 text-3xl font-bold',
    'headerText' => 'text-red-900',
    'headerTextDark' => 'dark:text-red-200',
    'subtitleClass' => 'mt-1 text-xs',
    'subTitleText' => 'text-red-600',
    'subTitleTextDark' => 'dark:text-red-300',
])

<div @class([$containerClass, $containerBorder, $containerBg, $containerBorderDark, $containerBgDark])>
    <div @class([$wrapperClass,])>
        <div>
            <p @class([$titleClass,$titleText,$titleTextDark])>
                {{ $title }}
            </p>
            <h3 @class([$headerClass,$headerText,$headerTextDark])>
                {{ $status }}
            </h3>
            <p @class([$subtitleClass,$subTitleText,$subTitleTextDark])>
                {{ $label }}
            </p>
        </div>
    </div>
</div>
