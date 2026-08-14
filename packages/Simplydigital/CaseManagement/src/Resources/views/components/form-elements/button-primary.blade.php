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
        wire:click="{{ $attributes->get('action') ?? '' }}"
        @if($attributes->get('loadingAttributeFalse'))
            wire:loading.attr='disabled'
        @endif
        @if($attributes->get('loadingTarget'))
            wire:target='{{ $attributes->get('loadingTarget') }}'
        @endif
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
    >
        @if($attributes->get('loadingTarget'))
            <span wire:loading.remove wire:target="{{ $attributes->get('loadingTarget') }}">
                Upload
            </span>
            <span wire:loading wire:target="{{ $attributes->get('loadingTarget') }}">
                Saving...
            </span>        
        @else
            {{ $attributes->get('label') }}
        @endif
    </button>
</div>
