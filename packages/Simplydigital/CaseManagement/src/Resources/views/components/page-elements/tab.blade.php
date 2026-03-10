@php
    $activeTab = 'inline-block p-4 text-blue-600 bg-gray-100 rounded-t-lg active dark:bg-gray-800 dark:text-blue-500';
    $inactiveTab = 'inline-block p-4 rounded-t-lg hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 dark:hover:text-gray-300';
@endphp

<div class="w-full">
    <ul class="flex flex-wrap text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:border-gray-700 dark:text-gray-400">
        @foreach ($tabHeaders as $header => $headerName)
        <li class="me-2">
            <a
                href="#bottom"
                class="{{ $header == $selectedTab ? $activeTab : $inactiveTab }}"
                wire:click="selectTab('{{ $header }}')"
            >
                {{ $headerName }}
            </a>
        </li>
        @endforeach
    </ul>
    <div class="p-3">
        <x-slot name="tabSelectedComponent">
            
        </x-slot>
    </div>

</div>