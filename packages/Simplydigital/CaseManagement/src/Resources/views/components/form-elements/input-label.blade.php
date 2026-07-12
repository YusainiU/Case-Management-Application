<div class="flex flex-row justify-start">
    <label 
        for="{{ $name }}" 
        @class([
            'form-label',
            'text-gray-800',
        ])
    >
        {{ $name }}
        @if($required ?? false)
            <span class="text-danger">*</span>
        @endif  
    </label>
</div>