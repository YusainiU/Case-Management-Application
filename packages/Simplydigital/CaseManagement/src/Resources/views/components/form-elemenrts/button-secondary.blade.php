<div>
    <button 
        type="button" 
        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
        wire:click="{{ $attributes->get('action') }}"
    >
        {{ $attributes->get('label') }}
    </button>
</div>