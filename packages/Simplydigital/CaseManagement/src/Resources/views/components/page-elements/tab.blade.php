@props([
    'tabHeaders' => [],
    'selectedTab' => null,
    'tabBgColorActive' => 'dark:bg-gray-800',
    'tabBgColorInactive' => 'dark:bg-gray-800',
    'tabBgColorInactiveHover' => 'dark:hover:bg-gray-800',
    'tabBgHover' => 'dark:hover:text-gray-300',
])

@php
    $baseTabClasses = 'inline-flex items-center px-4 py-3 rounded-t-lg transition-colors duration-150';

    $activeTabClasses = $baseTabClasses .
        " text-blue-600 bg-gray-100 border-b-2 border-blue-600 $tabBgColorActive dark:text-blue-500";

    $inactiveTabClasses = $baseTabClasses .
        " text-gray-500 hover:text-gray-600 hover:bg-gray-50 $tabBgColorInactive $tabBgColorInactiveHover $tabBgHover";
@endphp

<div class="w-full">
    <ul
        class="flex flex-wrap text-sm font-medium text-center border-b border-gray-200 dark:border-gray-700"
        role="tablist"
    >
        @foreach($tabHeaders as $header => $headerName)
            <li
                class="me-2"
                wire:key="tab-{{ $header }}"
                role="presentation"
            >
                <button
                    type="button"
                    wire:click="selectTab('{{ $header }}')"
                    class="{{ $header === $selectedTab ? $activeTabClasses : $inactiveTabClasses }}"
                >
                    {{ $headerName }}
                </button>
            </li>
        @endforeach
    </ul>

    <div class="p-4">
        {{ $slot }}
    </div>
</div>