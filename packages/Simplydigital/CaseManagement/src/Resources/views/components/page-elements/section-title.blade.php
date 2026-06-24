<div class="md:col-span-1 flex justify-between {{ $padding ?? 'p-4' }} shadow-[rgba(0,0,15,0.3)_0px_5px_4px_0px]">
    <div class="px-4 sm:px-0">        
        <a href="{{ url()->previous() ?: route('home') }}" class="flex flex-row gap-2 items-center mt-2">
            <x-casemanagement-ui-icon name="left" class="w-5 h-5 dark:text-gray-400" />
            <x-casemanagement::page-elements.title title="{{ $title }}" />
        </a>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ $description }}
        </p>
    </div>
    <div class="px-4 sm:px-0">
        {{ $aside ?? '' }}
    </div>
</div>
