@props(['field'])

@error($field)
    <div>    
        <span class="text-red-500 text-sm">
            {{ $message }}
        </span>
    </div>
@enderror