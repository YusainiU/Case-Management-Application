@props([
    'title' => 'No records found',
    'description' => 'Records will appear here when they are available.',
])

<div
    {{ $attributes->merge([
        'class' => 'mx-auto flex max-w-sm flex-col items-center text-center',
    ]) }}
>
    <div
        class="flex h-12 w-12 items-center justify-center rounded-full
               bg-slate-100 text-slate-400"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="1.5"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M17 20h5v-2a3 3 0 00-5.824-1M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.824-1M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
            />
        </svg>
    </div>

    <h3 class="mt-4 text-sm font-semibold text-slate-900">
        {{ $title }}
    </h3>

    <p class="mt-1 text-sm text-slate-500">
        {{ $description }}
    </p>
</div>
