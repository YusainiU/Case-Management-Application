@props([
    'section' => 'Section Title',
    'title' => 'Page Title',
    'description' => null,
    'showCreateButton' => false,
    'createButtonLabel' => 'Create New Item',
    'createAction' => 'actionName',
    'showBackButton' => false,
    'backButtonLabel' => 'Back',
    'showCustomButton' => false,
    'customButtonAction' => null,
    'customButtonLabel' => null,
])

<section
    {{ $attributes->merge([
        'class' => 'w-full overflow-hidden rounded-2xl border border-slate-200 bg-white text-slate-900 shadow-[0_2px_8px_rgba(15,23,42,0.05)]',
    ]) }}
>
    <div class="p-5 sm:p-6">

        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

            {{-- Heading --}}
            <div class="min-w-0 flex-1">

                {{-- Breadcrumb --}}
                <nav aria-label="Breadcrumb" class="mb-2">
                    <ol class="flex flex-wrap items-center gap-2 text-xs font-medium">
                        <li class="text-slate-500">
                            {{ $section }}
                        </li>

                        <li class="text-slate-300" aria-hidden="true">
                            <svg
                                class="h-3.5 w-3.5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m9 18 6-6-6-6"
                                />
                            </svg>
                        </li>

                        <li class="text-indigo-700" aria-current="page">
                            {{ $title }}
                        </li>
                    </ol>
                </nav>

                {{-- Title --}}
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    {{ $title }}
                </h1>

                {{-- Description --}}
                @if (filled($description))
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        {{ $description }}
                    </p>
                @endif

            </div>

            {{-- Page actions --}}
            @if ($showBackButton || $showCreateButton || $showCustomButton)
                <div class="flex shrink-0 flex-wrap items-center gap-2">

                    @if ($showBackButton)
                        <button
                            type="button"
                            onclick="window.history.back()"
                            class="inline-flex items-center justify-center gap-2 rounded-lg
                                   border border-slate-200 bg-white px-4 py-2.5
                                   text-sm font-semibold text-slate-700 shadow-sm
                                   transition hover:border-slate-300 hover:bg-slate-50
                                   focus:outline-none focus-visible:ring-2
                                   focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M10 19l-7-7m0 0 7-7m-7 7h18"
                                />
                            </svg>

                            {{ $backButtonLabel }}
                        </button>
                    @endif

                    @if ($showCreateButton)
                        <x-casemanagement::form-elements.button-primary
                            :label="$createButtonLabel"
                            :action="$createAction"
                        />
                    @endif

                  @if ($showCustomButton && $customButtonAction && $customButtonLabel)
                        <x-casemanagement::form-elements.button-primary
                            :label="$customButtonLabel"
                            :action="$customButtonAction"
                        />
                    @endif                    

                </div>
            @endif

        </div>
    </div>

    {{-- Subtle brand accent --}}
    <div class="h-px bg-slate-100"></div>
</section>
