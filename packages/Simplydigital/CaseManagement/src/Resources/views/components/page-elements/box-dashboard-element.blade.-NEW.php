
@props([
    'title' => 'Title',
    'content' => '0',
    'subTitle' => '',
    'action' => null,
    'containerClass' => '',
    'containerBorder' => 'border-gray-200',
    'containerBg' => 'bg-white',
    'titleText' => 'text-gray-700 dark:text-gray-300',
    'headerText' => 'text-gray-950 dark:text-white',
    'subTitleText' => 'text-gray-500 dark:text-gray-400',
])

<div class="h-full rounded-xl border border-gray-200 border-l-4
            border-l-indigo-500 bg-white shadow-sm
            transition-shadow duration-200 hover:shadow-md
            dark:border-gray-700 dark:bg-gray-900">
    <div class="flex min-h-36 items-center justify-between gap-4 p-5">
        <div class="min-w-0">
            <p class="text-sm font-medium {{ $titleText }}">
                {{ $title }}
            </p>

            <div class="mt-3 text-3xl font-bold tracking-tight
                        {{ $headerText }}">
                @if ($action)
                    <x-casemanagement::page-elements.href
                        :action="$action"
                        :label="$content"
                    />
                @else
                    {{ $content }}
                @endif
            </div>

            <p class="mt-2 text-sm {{ $subTitleText }}">
                {{ $subTitle }}
            </p>
        </div>

        <div class="flex size-11 shrink-0 items-center justify-center
                    rounded-lg bg-indigo-50 text-indigo-600
                    dark:bg-indigo-500/10 dark:text-indigo-400">
            <span class="text-xl font-semibold" aria-hidden="true">#</span>
        </div>
    </div>
</div>
