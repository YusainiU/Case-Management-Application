<div>
    <button 
        type="{{ $type ?? 'button' }}" 
        class="primary-button {{ $class ?? '' }}" 
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