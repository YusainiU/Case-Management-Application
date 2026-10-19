
@props([
    'title',
    'description',
    'items',
])

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

    {{-- Header --}}
    <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-6 sm:px-7">
        <div class="flex items-start gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.7"
                    stroke="currentColor"
                    class="h-6 w-6"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M8 7h8M8 11h8m-8 4h5m-9 5h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"
                    />
                </svg>
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-indigo-600">
                    Summary
                </p>

                <h3 class="mt-1 text-lg font-semibold tracking-tight text-slate-900 sm:text-xl">
                    {{ $title }}
                </h3>

                <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-500">
                    {{ $description }}
                </p>
            </div>
        </div>
    </div>

    {{-- Case details --}}
    <div class="p-5 sm:p-7">
        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($items as $item)
                <div
                    class="min-w-0 rounded-xl border border-slate-100 bg-white p-4 transition-colors hover:border-indigo-200 hover:bg-slate-50/70"
                    wire:key="case-summary-{{ $loop->index }}"
                >
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        {{ $item['label'] }}
                    </dt>

                    <dd class="mt-2 break-words text-sm font-medium leading-6 text-slate-900">
                        {{ $item['value'] }}
                    </dd>
                </div>
            @endforeach
        </dl>
    </div>

</div>

