@props([
    'title' => 'Modal Title',
    'widthsize' => 'md',
    'cancelThisWith' => 'closeThisModal',
    'confirm' => null,
    'cancelText' => 'Cancel',
    'confirmText' => 'Confirm',
    'showFooter' => true,
    'showConfirm' => true,
    'bgColor' => 'bg-gray-100',
    'borderColor' => 'border-gray-400',
    'border' => 'border rounded-lg shadow-xl',
    'width' => 'w-full',
    'mx' => 'mx-4'
])

<div>



        <div class="fixed inset-0 bg-opacity-75 flex items-center justify-center z-50 overflow-y-auto scrollable-area">
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
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 uppercase">
                        {{ $title ?? 'Modal' }}
                    </h3>
                    <button wire:click="{{ $cancelThisWith ?? 'closeModal' }}" class="text-gray-400 hover:text-gray-600">
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>
                <x-casemanagement::page-elements.seperator margin="0" />
                <!-- Content -->
                <div class="p-6 max-h-[70vh] overflow-y-auto">
                    {{ $slot }}
                </div>

                <!-- Footer -->
                @if ($showFooter ?? true)
                    <div class="flex justify-end gap-3 p-6 border-t border-gray-200">
                        <button wire:click="{{ $cancelThisWith ?? 'closeModal' }}"
                            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                            {{ $cancelText ?? 'Cancel' }}
                        </button>
                        @if ($showConfirm ?? true)
                            <button wire:click="{{ $confirm ?? 'confirm' }}"
                                class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                {{ $confirmText ?? 'Confirm' }}
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>

    

</div>
