<div class="flex items-center">
    <input 
        type="radio" 
        id="{{ $id ?? 'radio_' . uniqid() }}"
        name="{{ $name }}"
        value="{{ $value }}"
        wire:model="{{ $model }}"
        @if($checked ?? false) checked @endif
        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-2 focus:ring-blue-500"
    />
    @if($label ?? null)
        <label for="{{ $id ?? 'radio_' . uniqid() }}" class="ml-2 text-sm font-medium text-gray-900">
            {{ $label }}
        </label>
    @endif
</div>