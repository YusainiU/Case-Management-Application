<div class="md:col-span-1 flex justify-between p-4 shadow-[rgba(0,0,15,0.3)_0px_5px_4px_0px]">
    <div class="px-4 sm:px-0">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $title }}</h3>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ $description }}
        </p>
    </div>

    <div class="px-4 sm:px-0">
        {{ $aside ?? '' }}
    </div>
</div>