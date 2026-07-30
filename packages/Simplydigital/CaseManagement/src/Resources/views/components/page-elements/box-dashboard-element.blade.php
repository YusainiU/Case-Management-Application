@props([
    'title' => 'Title Here',
    'content' => 'content here',
    'subTitle' => 'Subtitle here',
    'containerClass' => 'relative aspect-video overflow-hidden rounded-xl border h-64',
    'containerBorder' => 'border-neutral-200',
    'containerBg' => 'bg-red-50',
    'containerBorderDark' => 'dark:border-neutral-700',
    'wrapperClass' => 'text-6xl flex flex-col items-center justify-center my-8',
    'titleClass' => 'text-3xl',
    'titleText' => 'text-red-700',
    'headerClass' => 'mt-2 font-bold',
    'headerText' => 'text-red-900',
    'subtitleClass' => 'mt-1 text-xl',
    'subTitleText' => 'text-red-600',
    'action' => null,            
])


<div @class([$containerClass,$containerBorder,$containerBorderDark,$containerBg])>
    <div @class([$wrapperClass])>
            <p @class([$titleClass,$titleText])>
                {{ $title }}
            </p>
            <h3 @class([$headerClass,$headerText,])>
                @if($action)
                    <x-casemanagement::page-elements.href action="{{ $action }}" label="{{ $content }}" />
                @else
                    {{ $content }}
                @endif
            </h3>
            <p @class([$subtitleClass,$subTitleText,])>
                {{ $subTitle }}
            </p>            
    </div>
</div>