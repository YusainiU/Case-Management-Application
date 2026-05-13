{{--
    To prepare options to correct array .. 
    use Simplydigital\CaseManagement\Services\UI;
     $this->options = UI::createArrayforMultiselect($options);  
--}}

@props([
    'options' => [],
    'placeholder' => 'Select Items ...',
])

<div x-data="multiselect({
    options: @js($options),
    selected: $wire.entangle('{{ $attributes->wire('model')->value() }}')
})" class="relative w-full">

    <div class="flex flex-wrap gap-2 p-2 border rounded-lg min-h-12 cursor-text"
        @click="open = true; $nextTick(() => $refs.search.focus())">

        <template x-for="item in selectedOptions" :key="item.value">
            <span class="inline-flex items-center gap-1 px-2 py-1 text-sm bg-blue-100 text-blue-800 rounded-md">
                <span x-text="item.label"></span>

                <button type="button" class="hover:text-red-500" @click.stop="remove(item.value)">
                    ×
                </button>
            </span>
        </template>

        <input x-ref="search" x-model="search" type="text"
            class="flex-1 border focus:ring-0 appearance-none text-black block border-gray-700 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
            :placeholder="selected.length ? '' : '{{ $placeholder }}'" @focus="open = true"
            @keydown.escape.window="open = false">
    </div>

    <div x-show="open" x-transition @mousedown.outside="open = false"
        class="absolute z-50 w-full mt-1 overflow-hidden bg-white border rounded-lg shadow-lg">
        <div class="max-h-64 overflow-y-auto">

            <template x-for="option in filteredOptions" :key="option.value">
                <div class="flex items-center justify-between px-3 py-2 cursor-pointer bg-white text-gray-900 hover:bg-gray-100"
                    @click.prevent="toggle(option)">
                    <span x-text="option.label"></span>

                    <svg x-show="selected.includes(option.value)" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </template>

            <div x-show="filteredOptions.length === 0" class="px-3 py-2 text-sm text-gray-500">
                No Items found.
            </div>
        </div>
    </div>
</div>
