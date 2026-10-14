{{-- 
    To prepare options into the correct array format:
    use Simplydigital\CaseManagement\Services\UI;
    $this->options = UI::createArrayforMultiselect($options);
--}}

@props([
    'options' => [],
    'placeholder' => 'Select items...',
])

<div
    x-data="multiselect({
        options: @js($options),
        selected: $wire.entangle('{{ $attributes->wire('model')->value() }}')
    })"
    class="relative w-full"
>
    {{-- Selected items and search input --}}
    <div
        class="flex min-h-12 flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 p-2.5 shadow-sm transition focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/20"
        @click="open = true; $nextTick(() => $refs.search.focus())"
    >
        <template x-for="item in selectedOptions" :key="item.value">
            <span
                class="inline-flex max-w-full items-center gap-1.5 rounded-lg border border-indigo-100 bg-indigo-50 px-2.5 py-1 text-sm font-medium text-gray-900"
            >
                <span class="truncate" x-text="item.label"></span>

                <button
                    type="button"
                    class="shrink-0 rounded text-gray-500 transition hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500/30"
                    :aria-label="'Remove ' + item.label"
                    @click.stop="remove(item.value)"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="size-4"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M4.22 4.22a.75.75 0 0 1 1.06 0L10 8.94l4.72-4.72a.75.75 0 1 1 1.06 1.06L11.06 10l4.72 4.72a.75.75 0 1 1-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 1 1-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 0 1 0-1.06Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>
            </span>
        </template>

        <input
            x-ref="search"
            x-model="search"
            type="text"
            autocomplete="off"
            class="min-w-24 flex-1 border-0 bg-gray-50 px-1 py-1 text-sm text-gray-900 outline-none placeholder:text-gray-400 focus:border-0 focus:outline-none focus:ring-0"
            :placeholder="selected.length ? '' : @js($placeholder)"
            @focus="open = true"
            @keydown.escape.window="open = false"
            @keydown.enter.prevent="
                if (filteredOptions.length > 0) {
                    toggle(filteredOptions[0])
                }
            "
        >
    </div>

    {{-- Options dropdown --}}
    <div
        x-cloak
        x-show="open"
        x-transition.origin.top
        @click.outside="open = false"
        class="absolute z-50 mt-2 w-full overflow-hidden rounded-xl border border-gray-200 bg-gray-50 shadow-lg"
    >
        <div class="max-h-64 overflow-y-auto p-1.5">
            <template x-for="option in filteredOptions" :key="option.value">
                <button
                    type="button"
                    class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-left text-sm text-gray-900 transition hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                    :class="selected.includes(option.value) ? 'bg-indigo-50' : 'bg-gray-50'"
                    :aria-pressed="selected.includes(option.value)"
                    @click.prevent="toggle(option)"
                >
                    <span class="min-w-0 truncate" x-text="option.label"></span>

                    <svg
                        x-show="selected.includes(option.value)"
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
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                </button>
            </template>

            <div
                x-show="filteredOptions.length === 0"
                class="px-3 py-6 text-center text-sm text-gray-500"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="mx-auto mb-2 size-5 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    aria-hidden="true"
                >
                    <circle cx="11" cy="11" r="7" stroke-width="1.5" />
                    <path stroke-linecap="round" stroke-width="1.5" d="m16 16 4 4" />
                </svg>

                No items found.
            </div>
        </div>

        {{-- Footer --}}
        <div
            x-show="selected.length > 0"
            class="flex items-center justify-between border-t border-gray-200 px-3 py-2"
        >
            <span
                class="text-xs text-gray-500"
                x-text="selected.length + (selected.length === 1 ? ' item selected' : ' items selected')"
            ></span>

            <button
                type="button"
                class="text-xs font-medium text-gray-600 transition hover:text-red-600"
                @click="selected = []; search = ''"
            >
                Clear all
            </button>
        </div>
    </div>
</div>
