@props([
    'title' => 'Modal Title',
    'widthsize' => 'md',
    'cancelThisWith' => 'closeThisModal',
    'cancelText' => 'Cancel',
    'showFooter' => true,
    'bgColor' => 'bg-gray-100 dark:bg-gray-900',
    'borderColor' => 'border-gray-400 dark:border-gray-700',
    'border' => 'border rounded-lg shadow-xl',
    'width' => 'w-full',
    'mx' => 'mx-4'
])

<div>



        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 scrollable-area">
            <div 
                @class([
                    "max-w-$widthsize",
                    $bgColor,
                    $borderColor,
                    $border,
                    $width,
                    $mx,
                ])
            >
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-200 p-6 dark:border-gray-700">
                    <h3 class="text-lg font-medium uppercase text-gray-900 dark:text-white">
                        {{ $title ?? 'Modal' }}
                    </h3>
                    <button wire:click="{{ $cancelThisWith ?? 'closeModal' }}" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>
                <x-casemanagement::page-elements.seperator margin="0" />
                <!-- Content -->
                <div class="max-h-[70vh] overflow-y-auto p-6 text-gray-700 dark:text-gray-300">
                    {{ $slot }}
                </div>

                <!-- Footer -->
                @if ($showFooter ?? true)
                    <div class="flex justify-end gap-3 border-t border-gray-200 p-6 dark:border-gray-700">
                        <button wire:click="{{ $cancelThisWith ?? 'closeModal' }}"
                            class="rounded-lg bg-gray-100 px-4 py-2 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                            {{ $cancelText ?? 'Cancel' }}
                        </button>
                    </div>
                @endif
            </div>
        </div>

    

</div>
