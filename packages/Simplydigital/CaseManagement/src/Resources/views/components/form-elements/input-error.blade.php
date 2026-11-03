@props(['field'])

@error($field)
    <div>    
        <span class="text-sm text-red-500 dark:text-red-400">
            {{ $message }}
        </span>
    </div>
@enderror