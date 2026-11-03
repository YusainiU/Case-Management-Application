{{-- 
  How to use ...
  <x-casemanagement::page-elements.banner header="Information" message="Hello" mode="important" /> 
--}}

@php
    use Simplydigital\CaseManagement\Services\UI;

    $class = match($mode) {
        'default' => UI::ALERT_DEFAULT,
        'information' => UI::ALERT_INFORMATION,
        'important' => UI::ALERT_IMPORTANT,
        'warning' => UI::ALERT_WARNING,
    };

    $icon = match($mode) {
        'default' => UI::DEFAULT_ICON_FILL,
        'information' => UI::INFORMATION_ICON_FILL,
        'important' => UI::IMPORTANT_ICON_FILL,
        'warning' => UI::WARNING_ICON_FILL,
    };

    $darkClass = match($mode) {
        'default' => 'dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100',
        'information' => 'dark:border-blue-400/20 dark:bg-blue-400/10 dark:text-blue-200',
        'important' => 'dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200',
        'warning' => 'dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200',
    };

    $darkIcon = match($mode) {
        'default' => 'dark:text-gray-300',
        'information' => 'dark:text-blue-300',
        'important' => 'dark:text-emerald-300',
        'warning' => 'dark:text-amber-300',
    };

@endphp

<div 
  x-data="{ open: true }" 
  x-show="open"
  x-transition:enter="transition ease-out duration-500"
  x-transition:enter-start="opacity-0 translate-y-2" 
  x-transition:enter-end="opacity-100 translate-y-0"
  x-transition:leave="transition ease-in duration-1000" 
  x-transition:leave-start="opacity-100 translate-y-0"
  x-transition:leave-end="opacity-0 translate-y-2"  
  x-init="setTimeout(() => open = false, 6000)"
  class="fixed bottom-4 right-20 z-50 w-96 px-4 py-3 shadow-lg rounded" role="alert"
>
    <section class="flex flex-col flex-wrap pt-12">
        <div class="flex flex-row flex-wrap justify-center">
            <div class="relative flex justify-center text-center m-2 h-24 w-64">
                <div
                    class="z-10 flex h-24 w-24 shrink-0 items-center justify-center rounded-full border bg-gray-100 {{ $icon }} {{ $darkIcon }} dark:border-gray-700 dark:bg-gray-900">
                    <x-casemanagement-ui-icon name="quote-left" />
                </div>
                <div
                    class="relative -ml-12 flex flex-col self-center py-2 pl-16 pr-4 text-left text-xs {{ $class }} {{ $darkClass }}">
                    {{-- Close Button --}}
                    <button type="button" @click="open = false"
                        class="absolute top-2 right-2 text-current hover:opacity-70">
                        ✕
                    </button>
                    <h3 class="text-lg">{{ $header }}</h3>
                    <p class="w-64 text-xs overflow-y-hidden overflow-x-auto">{{ $message }}</p>
                </div>
            </div>
        </div>
    </section>
</div>
