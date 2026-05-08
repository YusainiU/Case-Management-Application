<a 
    @if(isset($href))
        href="{{ $href }}"
    @endif
    {{ $attributes->merge([
        'class' => 'hover:underline hover:text-blue-500 cursor-pointer'
    ])}}
    @if(isset($action))
        wire:click="{{ $action }}"
    @endif
>
    {{ $label }}
</a>