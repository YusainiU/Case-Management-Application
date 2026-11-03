<div
    wire:poll.3s="refreshAnalysis"
    class="space-y-6"
>
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                AI Case Assistant
            </h2>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                AI-generated suggestions based on the case information.
            </p>
        </div>

        @can('case-assistant.analyze', $case)
            <button
                type="button"
                wire:click="analyze"
                wire:loading.attr="disabled"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:focus-visible:ring-offset-gray-900"
            >
                <span wire:loading.remove wire:target="analyze">
                    Analyze Case
                </span>

                <span wire:loading wire:target="analyze">
                    Starting...
                </span>
            </button>
        @endcan
    </div>

    @if ($analysis?->status === 'pending')
        <div class="rounded-md border border-blue-200 bg-blue-50 p-4 text-blue-900 dark:border-blue-400/20 dark:bg-blue-400/10 dark:text-blue-200">
            AI analysis is waiting to start...
        </div>
    @endif

    @if ($analysis?->status === 'processing')
        <div class="rounded-md border border-indigo-200 bg-indigo-50 p-4 text-indigo-900 dark:border-indigo-400/20 dark:bg-indigo-400/10 dark:text-indigo-200">
            AI is analyzing this case...
        </div>
    @endif

    @if ($analysis?->status === 'failed')
        <div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-900 dark:border-red-400/20 dark:bg-red-400/10 dark:text-red-200">
            <p class="font-medium">
                AI analysis failed.
            </p>

            <p class="mt-1 text-sm text-red-800 dark:text-red-300">
                Please try again.
            </p>
        </div>
    @endif

    @if ($result)
        <div class="space-y-6">

            {{-- Summary --}}
            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white">
                    Summary
                </h3>

                <p class="mt-2 text-sm">
                    {{ $result->summary }}
                </p>
            </section>

            {{-- Classification --}}
            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white">
                    Suggested Classification
                </h3>

                <div class="mt-3 grid grid-cols-2 gap-4">

                    <div class="rounded-md border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            Category
                        </div>

                        <div class="mt-1 font-medium text-gray-900 dark:text-gray-100">
                            {{ $result->suggestedCategory ?? 'No suggestion' }}
                        </div>
                    </div>

                    <div class="rounded-md border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            Priority
                        </div>

                        <div class="mt-1 font-medium text-gray-900 dark:text-gray-100">
                            {{ $result->suggestedPriority ?? 'No suggestion' }}
                        </div>
                    </div>

                </div>
            </section>

            {{-- Next actions --}}
            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white">
                    Suggested Next Actions
                </h3>

                <ul class="mt-3 space-y-2">
                    @forelse (
                        $result->suggestedNextActions
                        as $action
                    )
                        <li class="rounded-md border border-gray-200 bg-white p-3 text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            {{ $action }}
                        </li>
                    @empty
                        <li class="text-sm text-gray-500 dark:text-gray-400">
                            No next actions suggested.
                        </li>
                    @endforelse
                </ul>
            </section>

            {{-- Customer response --}}
            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white">
                    Customer Response Draft
                </h3>

                @if ($result->customerResponseDraft)
                    <div class="mt-3 rounded-md border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                        <div class="whitespace-pre-line text-sm text-gray-800 dark:text-gray-200">
                            {{ $result->customerResponseDraft }}
                        </div>
                    </div>

                    @can('case-assistant.apply', $case)
                        <button
                            type="button"
                            class="mt-3 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            Use Draft
                        </button>
                    @endcan
                @else
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        No response draft was generated.
                    </p>
                @endif
            </section>

            {{-- Warnings --}}
            @if (count($result->warnings))
                <section>
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                        Warnings
                    </h3>

                    <ul class="mt-3 space-y-2">
                        @foreach ($result->warnings as $warning)
                            <li class="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200">
                                {{ $warning }}
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

        </div>
    @endif
</div>
