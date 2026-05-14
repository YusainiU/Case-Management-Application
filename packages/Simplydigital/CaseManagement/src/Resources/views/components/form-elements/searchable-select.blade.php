
{{--

    Example of options array format ..

    $customerOptions = [
        ['id' => 1, 'name' => 'Acme Ltd'],
        ['id' => 2, 'name' => 'Globex Corporation'],
        ['id' => 3, 'name' => 'Umbrella Inc'],
    ];

    To use ...

    <x-casemanagement::form-elements.searchable-select
        name="Customer"
        :required="true"
        :options="$customerOptions"
        model="customer_id"
    />    

--}}

@props([
    'name' => '',
    'options' => [],
    'required' => false,
    'placeholder' => 'Search...',
    'valueField' => 'id',
    'textField' => 'name',
    'wireModel' => null,
])

@php
    if (empty($wireModel)) {
        throw new \Exception('wireModel is required for searchable-select component');
    }
@endphp

<div
    x-data="{
        open: false,
        search: '',
        highlightedIndex: 0,
        selectedValue: null,
        selectedText: '',
        options: [],
        
        init() {
            // Initialize options from props
            this.options = {{ \Illuminate\Support\Js::from($options) }};
            
            // Set up Livewire watcher for the model
            this.$watch('selectedValue', (value) => {
                @this.set('{{ $wireModel }}', value);
            });
            
            // Initialize from existing Livewire value
            this.selectedValue = @this.get('{{ $wireModel }}');
            
            // Find and set the selected text based on the value
            if (this.selectedValue) {
                const selectedOption = this.options.find(opt => opt['{{ $valueField }}'] == this.selectedValue);
                if (selectedOption) {
                    this.selectedText = selectedOption['{{ $textField }}'];
                    this.search = this.selectedText;
                }
            }
            
            // Watch for search changes to ensure dropdown shows/hides properly
            this.$watch('search', () => {
                // Reset highlighted index when search changes
                this.highlightedIndex = 0;
                
                // If search is cleared and dropdown is open, keep it open
                if (this.search === '' && this.open) {
                    // Force a re-evaluation of filteredOptions
                    this.$nextTick(() => {
                        // This ensures the DOM updates
                    });
                }
            });
            
            // Watch for Livewire updates to the model from the server side
            @this.watch('{{ $wireModel }}', (value) => {
                if (value !== this.selectedValue) {
                    this.selectedValue = value;
                    
                    // Update the displayed text when the model changes externally
                    if (value) {
                        const selectedOption = this.options.find(opt => opt['{{ $valueField }}'] == value);
                        if (selectedOption) {
                            this.selectedText = selectedOption['{{ $textField }}'];
                            this.search = this.selectedText;
                        }
                    } else {
                        this.selectedText = '';
                        this.search = '';
                    }
                }
            });
            
            // Watch for options updates
            this.$watch('options', () => {
                // If options change, try to refresh the selected text
                if (this.selectedValue) {
                    const selectedOption = this.options.find(opt => opt['{{ $valueField }}'] == this.selectedValue);
                    if (selectedOption) {
                        this.selectedText = selectedOption['{{ $textField }}'];
                        this.search = this.selectedText;
                    }
                }
                this.highlightedIndex = 0;
            });
        },
        
        get filteredOptions() {
            if (!this.options || this.options.length === 0) {
                return [];
            }
            
            // If search is empty, return all options
            if (!this.search || this.search.trim() === '') {
                return this.options;
            }
            
            // Use Set to ensure unique results based on valueField
            const uniqueOptions = new Map();
            
            this.options.forEach(option => {
                const key = option['{{ $valueField }}'];
                if (!uniqueOptions.has(key)) {
                    uniqueOptions.set(key, option);
                }
            });
            
            const uniqueOptionsArray = Array.from(uniqueOptions.values());
            
            // Filter based on search
            return uniqueOptionsArray.filter(option => {
                const text = option['{{ $textField }}'] || '';
                return text.toLowerCase().includes(this.search.toLowerCase());
            });
        },
        
        selectOption(option) {
            // Store the selected option's text and value
            this.selectedText = option['{{ $textField }}'];
            this.selectedValue = option['{{ $valueField }}'];
            
            // Set the search input to show the selected text
            this.search = this.selectedText;
            
            // Close the dropdown
            this.open = false;
            this.highlightedIndex = 0;
            
            // Update Livewire through the watcher
            @this.set('{{ $wireModel }}', option['{{ $valueField }}']);
        },
        
        moveDown() {
            if (this.highlightedIndex < this.filteredOptions.length - 1) {
                this.highlightedIndex++;
            }
        },
        
        moveUp() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            }
        },
        
        selectHighlighted() {
            let option = this.filteredOptions[this.highlightedIndex];
            if (option) {
                this.selectOption(option);
            }
        },
        
        handleFocus() {
            this.open = true;
            // Only clear search if we're not editing an existing selection
            if (!this.selectedText) {
                this.search = '';
            }
            this.highlightedIndex = 0;
            
            // Force filteredOptions to re-evaluate
            this.$nextTick(() => {
                // This ensures the computed property updates
            });
        },
        
        handleInput() {
            // Ensure dropdown stays open when typing
            if (!this.open) {
                this.open = true;
            }
            // Reset highlighted index when typing
            this.highlightedIndex = 0;
        },
        
        handleClear() {
            this.selectedValue = null;
            this.selectedText = '';
            this.search = '';
            this.open = false;
            @this.set('{{ $wireModel }}', null);
        }
    }"
    class="relative w-full"
    @click.away="open = false"
>
    {{-- <label class="mb-2 block text-sm font-medium text-gray-700">
        {{ $name }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label> --}}

    <div class="relative">
        <input
            type="text"
            x-model="search"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            class="appearance-none w-full text-black block border border-gray-700 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
            :class="{'pr-10': selectedValue}"
            @focus="handleFocus()"
            @input="handleInput()"
            @keydown.arrow-down.prevent="moveDown()"
            @keydown.arrow-up.prevent="moveUp()"
            @keydown.enter.prevent="selectHighlighted()"
        />
        
        <!-- Clear button -->
        <button
            x-show="selectedValue"
            @click="handleClear()"
            type="button"
            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <div
        x-show="open"
        x-transition
        class="absolute z-50 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-gray-400 text-gray-900 shadow-lg bg-white"
    >
        <template x-if="filteredOptions.length > 0">
            <ul>
                <template
                    x-for="(option, index) in filteredOptions"
                    :key="option['{{ $valueField }}']"
                >
                    <li
                        @click="selectOption(option)"
                        @mouseenter="highlightedIndex = index"
                        class="cursor-pointer px-4 py-2 hover:bg-gray-100"
                        :class="highlightedIndex === index ? 'bg-gray-200' : ''"
                        x-text="option['{{ $textField }}']"
                    ></li>
                </template>
            </ul>
        </template>

        <template x-if="filteredOptions.length === 0">
            <div class="px-4 py-3 text-sm text-gray-500">
                No results found.
            </div>
        </template>
    </div>

    @error($wireModel)
        <p class="mt-1 text-sm text-red-500">
            {{ $message }}
        </p>
    @enderror
</div>