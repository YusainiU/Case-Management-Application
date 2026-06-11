@props([
    "hover_on" => "bg-gray-400 text-gray-100",
    "hover_off" => "bg-gray-500 text-gray-100",
    "action_hover_on" => "confirmSetAction()",
    "hover_on_label" => "Action On",
    "hover_off_label" => "Action Off",
    "size" => "normal",
])

@php
    $px = 'px-6';
    $py = 'py-3';
    $rounded = 'rounded';
    if(isset($size) && $size == 'small'){
        $px = 'px-1';
        $py = 'py-1';
        $rounded = 'rounded-md';
    }
@endphp

<div x-data="{ hover: false }">
    <button
        type="button"
        x-on:mouseenter="hover = true"
        x-on:mouseleave="hover = false"
        x-bind:class="hover ? '{{ $hover_on }}' : '{{ $hover_off }}'"
        x-on:click="
            if (hover) {
                $wire.{{ $action_hover_on }}
            }
        "
        class="
            {{ $px }}
            {{ $py }}
            text-left 
            text-xs 
            font-medium   
            tracking-wider 
            cursor-pointer 
            rounded 
            transition-colors 
            duration-300"        
    >
        <span x-text="hover ? '{{ $hover_on_label }}' : '{{ $hover_off_label }}'"></span>
    </button>
</div>