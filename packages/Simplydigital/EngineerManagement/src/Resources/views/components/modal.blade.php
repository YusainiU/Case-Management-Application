<div>

    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    {{ $title ?? 'Modal' }}
                </h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <span class="text-2xl">&times;</span>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6">
                {{ $slot }}
            </div>

            <!-- Footer -->
            @if ($showFooter ?? true)
                <div class="flex justify-end gap-3 p-6 border-t border-gray-200">
                    <button wire:click="closeModal"
                        class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        {{ $cancelText ?? 'Cancel' }}
                    </button>
                    <button wire:click="confirm" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        {{ $confirmText ?? 'Confirm' }}
                    </button>
                </div>
            @endif
        </div>
    </div>

</div>
