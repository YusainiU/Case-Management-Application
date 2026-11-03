<a 
    @if(isset($href))
        href="{{ $href }}"
    @endif
    {{ $attributes->merge([
        'class' => 'cursor-pointer text-blue-600 hover:text-blue-500 hover:underline dark:text-blue-400 dark:hover:text-blue-300'
    ])}}
    @if(isset($action))
        wire:click="{{ $action }}"
    @endif
>
    {{ $label }}
</a>