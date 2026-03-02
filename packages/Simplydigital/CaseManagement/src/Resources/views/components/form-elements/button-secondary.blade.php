<div>
    <button 
        type="button" 
        class="px-6 py-3 text-left text-xs font-medium text-gray-200 bg-gray-500 uppercase tracking-wider cursor-pointer rounded hover:bg-gray-400 transition-colors duration-300"
        wire:click="{{ $attributes->get('action') }}"
    >
        {{ $attributes->get('label') }}
    </button>
</div>