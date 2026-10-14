@props([
    'name' => '',
    'required' => false,
])

<div class="mb-2">
    <label
        @if($name)
            for="{{ $name }}"
        @endif
        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
    >
        {{ $name }}

        @if ($required)
            <span
                class="ml-0.5 text-red-500 dark:text-red-400"
                aria-hidden="true"
            >*</span>
            <span class="sr-only"> (required)</span>
        @endif
    </label>
</div>
