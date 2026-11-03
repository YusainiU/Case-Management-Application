{{-- 
    How To use ..
    <x-casemanagement::page-elements.banner-simple header="Information" message="Hello" mode="important" /> 
--}}


@php

    use Simplydigital\CaseManagement\Services\UI;

    $class = match($mode) {
        'default' => UI::ALERT_DEFAULT,
        'information' => UI::ALERT_INFORMATION,
        'important' => UI::ALERT_IMPORTANT,
        'warning' => UI::ALERT_WARNING,
    }

    $darkClass = match($mode) {
        'default' => 'dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100',
        'information' => 'dark:border-blue-400/20 dark:bg-blue-400/10 dark:text-blue-200',
        'important' => 'dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200',
        'warning' => 'dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200',
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

    class="
      relative
      {{ $class }} 
      {{ $darkClass }}
      border-t 
      border-b 
      px-4 
      py-3 
      shadow-lg 
      rounded"
    role="alert"
>
    {{-- Close Button --}}
    <button type="button" @click="open = false" class="absolute top-2 right-2 text-current hover:opacity-70">
        ✕
    </button>
    <p class="font-bold">{{ $header }}</p>
    <p class="text-sm">
        {{ $message }}
    </p>
</div>
