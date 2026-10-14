{{--
    Searchable Select

    Options format:
    [
        ['id' => 1, 'name' => 'Acme Ltd'],
        ['id' => 2, 'name' => 'Globex Corporation'],
    ]

    Usage:
    <x-casemanagement::form-elements.searchable-select
        name="Customer"
        :required="true"
        :options="$customerOptions"
        wireModel="customerId"
        placeholder="Search customers..."
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
        throw new \Exception(
            'wireModel is required for searchable-select component'
        );
    }

    $hasError = $errors->has($wireModel);
    $inputId = $attributes->get('id', 'searchable-' . $wireModel);
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
            this.options = {{ \Illuminate\Support\Js::from($options) }};

            this.selectedValue = @this.get('{{ $wireModel }}');
            this.syncSelectedText();

            this.$watch('selectedValue', (value) => {
                @this.set('{{ $wireModel }}', value);
            });

            this.$watch('search', () => {
                this.highlightedIndex = 0;
            });

            @this.watch('{{ $wireModel }}', (value) => {
                if (value != this.selectedValue) {
                    this.selectedValue = value;
                    this.syncSelectedText();
                }
            });

            this.$watch('options', () => {
                this.syncSelectedText();
                this.highlightedIndex = 0;
            });
        },

        syncSelectedText() {
            if (
                this.selectedValue !== null &&
                this.selectedValue !== ''
            ) {
                const selectedOption = this.options.find(
                    option =>
                        option['{{ $valueField }}'] == this.selectedValue
                );

                if (selectedOption) {
                    this.selectedText =
                        selectedOption['{{ $textField }}'] ?? '';

                    this.search = this.selectedText;
                    return;
                }
            }

            this.selectedText = '';
            this.search = '';
        },

        get filteredOptions() {
            if (!Array.isArray(this.options)) {
                return [];
            }

            const uniqueOptions = new Map();

            this.options.forEach(option => {
                const key = option['{{ $valueField }}'];

                if (!uniqueOptions.has(key)) {
                    uniqueOptions.set(key, option);
                }
            });

            const query = this.search.trim().toLowerCase();

            return Array.from(uniqueOptions.values()).filter(option => {
                const text = String(
                    option['{{ $textField }}'] ?? ''
                );

                return text.toLowerCase().includes(query);
            });
        },

        handleFocus() {
            this.open = true;
            this.highlightedIndex = 0;

            if (this.selectedValue !== null && this.selectedValue !== '') {
                this.search = '';
            }
        },

        handleInput() {
            this.open = true;
            this.highlightedIndex = 0;

            if (this.search !== this.selectedText) {
                this.selectedValue = null;
                this.selectedText = '';
            }
        },

        selectOption(option) {
            this.selectedText = option['{{ $textField }}'] ?? '';
            this.selectedValue = option['{{ $valueField }}'];

            this.search = this.selectedText;
            this.open = false;
            this.highlightedIndex = 0;

            @this.set(
                '{{ $wireModel }}',
                option['{{ $valueField }}']
            );
        },

        moveDown() {
            if (!this.open) {
                this.open = true;
                return;
            }

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
            const option = this.filteredOptions[this.highlightedIndex];

            if (option) {
                this.selectOption(option);
            }
        },

        handleClear() {
            this.selectedValue = null;
            this.selectedText = '';
            this.search = '';
            this.highlightedIndex = 0;
            this.open = false;

            @this.set('{{ $wireModel }}', null);
        }
    }"
    class="relative w-full"
    @click.outside="open = false"
    @keydown.escape="open = false"
>
    {{-- Label --}}
    @if ($name)
        <label
            for="{{ $inputId }}"
            class="mb-2 block text-sm font-medium text-gray-700"
        >
            {{ $name }}

            @if ($required)
                <span class="ml-0.5 text-red-500" aria-hidden="true">*</span>
                <span class="sr-only">(required)</span>
            @endif
        </label>
    @endif

    {{-- Search input --}}
    <div class="relative">
        <div
            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="size-4 text-gray-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                aria-hidden="true"
            >
                <circle cx="11" cy="11" r="7" stroke-width="1.5" />
                <path
                    stroke-linecap="round"
                    stroke-width="1.5"
                    d="m16 16 4 4"
                />
            </svg>
        </div>

        <input
            id="{{ $inputId }}"
            type="text"
            x-model="search"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            role="combobox"
            aria-autocomplete="list"
            aria-haspopup="listbox"
            :aria-expanded="open.toString()"
            @focus="handleFocus()"
            @input="handleInput()"
            @keydown.arrow-down.prevent="moveDown()"
            @keydown.arrow-up.prevent="moveUp()"
            @keydown.enter.prevent="selectHighlighted()"
            @required($required)
            @class([
                'block w-full rounded-xl border bg-gray-50',
                'py-3 pl-10 pr-10 text-sm text-gray-900',
                'placeholder:text-gray-400 shadow-sm transition',
                'focus:outline-none focus:ring-2',
                'focus:ring-indigo-500/20',
                'focus:border-indigo-500',
                'border-red-300 focus:border-red-500 focus:ring-red-500/20' => $hasError,
                'border-gray-200' => !$hasError,
            ])
            @if ($hasError)
                aria-invalid="true"
                aria-describedby="{{ $inputId }}-error"
            @endif
        />

        {{-- Clear selection --}}
        <button
            x-cloak
            x-show="selectedValue !== null && selectedValue !== ''"
            @click="handleClear()"
            type="button"
            aria-label="Clear selection"
            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 transition hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500/30"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="size-4"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
            >
                <path
                    d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"
                />
            </svg>
        </button>
    </div>

    {{-- Dropdown --}}
    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-x-0 z-50 mt-2 max-h-64 overflow-y-auto rounded-xl border border-gray-200 bg-gray-50 p-1.5 shadow-xl shadow-gray-900/10"
    >
        <template x-if="filteredOptions.length > 0">
            <ul role="listbox" class="space-y-0.5">
                <template
                    x-for="(option, index) in filteredOptions"
                    :key="option['{{ $valueField }}']"
                >
                    <li
                        role="option"
                        :aria-selected="option['{{ $valueField }}'] == selectedValue"
                        @click="selectOption(option)"
                        @mouseenter="highlightedIndex = index"
                        :class="highlightedIndex === index
                            ? 'bg-indigo-50 text-indigo-700'
                            : 'bg-gray-50 text-gray-900 hover:bg-gray-100'"
                        class="flex cursor-pointer items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-sm transition-colors"
                    >
                        <span
                            class="min-w-0 truncate"
                            x-text="option['{{ $textField }}']"
                        ></span>

                        <svg
                            x-cloak
                            x-show="option['{{ $valueField }}'] == selectedValue"
                            xmlns="http://www.w3.org/2000/svg"
                            class="size-4 shrink-0 text-indigo-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m5 13 4 4L19 7"
                            />
                        </svg>
                    </li>
                </template>
            </ul>
        </template>

        <template x-if="filteredOptions.length === 0">
            <div class="px-3 py-8 text-center">
                <div
                    class="mx-auto mb-2 flex size-9 items-center justify-center rounded-full bg-gray-100 text-gray-500"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="size-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        aria-hidden="true"
                    >
                        <circle cx="11" cy="11" r="7" stroke-width="1.5" />
                        <path
                            stroke-linecap="round"
                            stroke-width="1.5"
                            d="m16 16 4 4"
                        />
                    </svg>
                </div>

                <p class="text-sm font-medium text-gray-700">
                    No results found
                </p>

                <p class="mt-1 text-xs text-gray-500">
                    Try a different search term.
                </p>
            </div>
        </template>
    </div>

    {{-- Validation --}}
    @if ($hasError)
        <p
            id="{{ $inputId }}-error"
            class="mt-2 flex items-start gap-1.5 text-sm text-red-600"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="mt-0.5 size-4 shrink-0"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
            >
                <path
                    fill-rule="evenodd"
                    d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9 5.75a1 1 0 1 1 2 0v4a1 1 0 0 1-2 0v-4Zm1 7a1.25 1.25 0 1 0 0 2.5 1.25 1.25 0 0 0 0-2.5Z"
                    clip-rule="evenodd"
                />
            </svg>

            <span>{{ $errors->first($wireModel) }}</span>
        </p>
    @endif
</div>

