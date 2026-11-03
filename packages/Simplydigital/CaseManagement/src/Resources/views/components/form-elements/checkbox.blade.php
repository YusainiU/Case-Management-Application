<div class="flex items-center">
    <input 
        type="checkbox" 
        id="{{ $id ?? 'radio_' . uniqid() }}"
        name="{{ $name }}"
        {{-- value="{{ $value }}" --}}
        wire:model="{{ $model }}"
        @if($checked ?? false) checked @endif
        class="h-4 w-4 border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:focus:ring-blue-400"
    />
    @if($label ?? null)
        <label for="{{ $id ?? 'radio_' . uniqid() }}" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-200">
            {{ $label }}
        </label>
    @endif
</div>