@props(['options', 'property'])

<div x-data="{
        open: false,
        search: '',
        selectedId: @entangle($property).live,
        options: @js($options),
        get filteredOptions() {
            if (!this.search.trim()) return this.options;
            return this.options.filter(option => option.name.toLowerCase().includes(this.search.toLowerCase()));
        },
        get selectedOption() {
            if (!this.selectedId) return null;
            return this.options.find(option => option.id === this.selectedId);
        },
        selectOption(option) {
            this.selectedId = option.id;
            this.open = false;
        },
        selectedValue: @entangle($property).live,


    }"
>
    <div  class="relative mt-2">
        <button @click="open = !open" type="button" class="relative cursor-pointer w-full text-normal border-gray-300 rounded-md bg-white py-2 pl-3 pr-10 text-left shadow-sm ring-1 ring-inset ring-gray-300  focus:border-sky-500 focus:ring-sky-500">
            <span class="block truncate" x-text="selectedOption ? selectedOption.name : 'Select an option'"></span>
            <input hidden name="selected_value" x-model="selectedValue" />
            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                <svg class="h-5 w-5 text-gray-800" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.2a.75.75 0 011.06.04l2.7 2.908 2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.04-1.06z" clip-rule="evenodd" />
                </svg>
            </span>
        </button>

        <div x-show="open" @click.away="open = false" x-cloak class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg">
            <input id="xInputId" type="text" x-model="search" placeholder="Search..." class="block w-full border-0 border-b border-gray-300 bg-white pb-2 pl-3 text-left focus:ring-0">
            <ul class="max-h-60 overflow-auto">
                <template x-for="option in filteredOptions" :key="option.id">
                    <li 
                        @click="selectOption(option);selectedValue=selectedOption.id;" 
                        class="cursor-pointer select-none py-2 pl-3 pr-9 hover:bg-gray-100 hover:text-gray-800" 
                        :class="{'bg-sky-600 text-white': selectedOption && selectedOption.id === option.id}"
                    >
                        <span x-text="option.name" class="font-normal block truncate"></span>
                    </li>
                </template>
            </ul>
        </div>
    </div>
</div>
