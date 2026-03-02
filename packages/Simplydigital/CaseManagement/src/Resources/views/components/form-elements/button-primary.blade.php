<div>
    <button 
        type="{{ $type ?? 'button' }}" 
        class="px-6 py-3 text-left text-xs font-medium text-gray-200 bg-blue-500 uppercase tracking-wider cursor-pointer rounded hover:bg-blue-600 transition-colors duration-300" 
        wire:click="{{ $attributes->get('action') ?? '' }}"
    >    
        {{ $attributes->get('label') }}
    </button>
</div>

<style>
    .primary-button {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .primary-button:hover {
        background-color: #0056b3;
    }
</style>