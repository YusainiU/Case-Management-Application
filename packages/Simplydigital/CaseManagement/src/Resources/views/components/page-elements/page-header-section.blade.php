@props([
    'section' => 'Section Title',
    'title' => 'Page Title',
    'description' => null,
    'showCreateButton' => false,
    'createButtonLabel' => 'Create New Item',
    'createAction' => 'actionName',
    'showBackButton' => false,
    'backButtonLabel' => 'Back',    
])

{{-- Page heading --}}
<section class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

    <div class="min-w-0">
        {{-- Breadcrumb --}}
        <div class="mb-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
            <span>{{ $section }}</span>

            <span class="text-slate-300 dark:text-slate-600">/</span>

            <span class="font-medium text-slate-700 dark:text-slate-200">
                {{ $title }}
            </span>
        </div>

        {{-- Page title --}}
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-3xl">
            {{ $title }}
        </h1>

        {{-- Description --}}
        @if ($description)
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                {{ $description }}
            </p>
        @endif
    </div>

        @if ($showBackButton)
            <button
                type="button"
                onclick="window.history.back()"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300
                       bg-white px-4 py-2 text-sm font-medium text-slate-700
                       shadow-sm transition hover:bg-slate-50
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                       dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200
                       dark:hover:bg-slate-700 dark:focus:ring-offset-slate-900"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"
                    />
                </svg>

                {{ $backButtonLabel }}
            </button>
        @endif    

    {{-- Optional action button --}}
    @if ($showCreateButton)
        <div class="shrink-0">
            <x-casemanagement::form-elements.button-primary
                :label="$createButtonLabel"
                :action="$createAction"
            />
        </div>
    @endif

</section>