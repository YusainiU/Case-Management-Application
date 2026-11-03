@props([
    'tabHeaders' => [],
    'selectedTab' => null,
])

@php
    $baseTabClasses = 'relative inline-flex shrink-0 items-center justify-center
        whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium
        transition-colors duration-150 ease-in-out
        focus:outline-none focus-visible:ring-2
        focus-visible:ring-inset focus-visible:ring-indigo-500';

    $activeTabClasses = $baseTabClasses . '
        border-indigo-600 bg-white text-indigo-700
        dark:border-indigo-400 dark:bg-gray-900 dark:text-indigo-300';

    $inactiveTabClasses = $baseTabClasses . '
        border-transparent bg-white text-slate-500
        hover:border-slate-300 hover:bg-slate-50 hover:text-slate-800
        dark:bg-gray-900 dark:text-slate-400
        dark:hover:border-slate-700 dark:hover:bg-gray-800 dark:hover:text-slate-200';
@endphp

<div {{ $attributes->merge([
    'class' => 'w-full min-w-0 bg-white text-slate-800 dark:bg-gray-900 dark:text-slate-200',
]) }}>

    {{-- Tab navigation --}}
    <div class="overflow-x-auto border-b border-slate-200 dark:border-slate-800">
        <ul
            class="flex min-w-max items-center gap-1 px-2"
            role="tablist"
            aria-label="Customer information sections"
        >
            @foreach ($tabHeaders as $header => $headerName)
                <li
                    wire:key="tab-{{ $header }}"
                    role="presentation"
                >
                    <button
                        type="button"
                        id="tab-{{ $header }}"
                        role="tab"
                        wire:click="selectTab('{{ $header }}')"
                        aria-selected="{{ $header === $selectedTab ? 'true' : 'false' }}"
                        aria-controls="panel-{{ $header }}"
                        tabindex="{{ $header === $selectedTab ? '0' : '-1' }}"
                        class="{{ $header === $selectedTab ? $activeTabClasses : $inactiveTabClasses }}"
                    >
                        {{ $headerName }}
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Tab content --}}
    <div
        id="panel-{{ $selectedTab }}"
        role="tabpanel"
        aria-labelledby="tab-{{ $selectedTab }}"
        tabindex="0"
        class="min-w-0 bg-white p-4 text-slate-800 focus:outline-none
               focus-visible:ring-2 focus-visible:ring-inset
               focus-visible:ring-indigo-500 sm:p-5
               dark:bg-gray-900 dark:text-slate-200"
    >
        {{ $slot }}
    </div>

</div>
