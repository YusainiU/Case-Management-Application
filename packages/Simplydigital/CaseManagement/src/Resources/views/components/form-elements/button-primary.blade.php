@php
    $px = 'px-6';
    $py = 'py-3';
    if(isset($size) && $size == 'small'){
        $px = 'px-1';
        $py = 'py-1';
    }
@endphp

<div>
    <button 
        type="{{ $type ?? 'button' }}" 
        class="
            {{ $px }} 
            {{ $py }} 
            text-left 
            text-xs 
            font-medium 
            text-gray-200 
            bg-blue-500 
            uppercase 
            tracking-wider 
            cursor-pointer 
            rounded 
            hover:bg-blue-600 
            transition-colors 
            duration-300"

        wire:click="{{ $attributes->get('action') ?? '' }}"
    >    
        {{ $attributes->get('label') }}
    </button>
</div>
