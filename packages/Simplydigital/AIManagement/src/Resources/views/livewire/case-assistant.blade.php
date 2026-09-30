<div wire:poll.3s="refreshAnalysis" class="space-y-6">
    <div class="flex items-center justify-between">

        <div>
            <h2 class="text-lg font-semibold text-gray-600">
                AI Case Assistant
            </h2>

            <p class="text-sm text-gray-600 mt-4">
                AI-generated suggestions based on the case information.
            </p>
        </div>

        @can('case-assistant.analyze', $case)
            @if (!$result)
                <button type="button" wire:click="analyze" wire:loading.attr="disabled"
                    class="rounded-md px-4 py-2 text-sm font-medium bg-blue-600 text-gray-200">
                    <div>
                        <span wire:loading.remove wire:target="analyze">
                            Analyze Case
                        </span>

                        <span wire:loading wire:target="analyze">
                            Starting...
                        </span>
                    </div>
                </button>
            @endif
        @endcan
    </div>

    <div class="text-gray-600">
        @if ($analysis?->status === 'pending')
            <div class="rounded-md border p-4 border-gray-500">
                AI analysis is waiting to start...
            </div>
        @endif

        @if ($analysis?->status === 'processing')
            <div class="rounded-md border p-4 border-gray-500">
                AI is analyzing this case...
            </div>
        @endif

        @if ($analysis?->status === 'failed')
            <div class="rounded-md border p-4 border-gray-500">
                <p class="font-medium text-red-600">
                    AI analysis failed.
                </p>

                <p class=" text-red-600 text-sm italic my-3">
                    {{ $analysis->error }}
                </p>

                <p class="mt-1 text-sm">
                    Please try again.
                </p>
            </div>
        @endif
    </div>

    @if ($result)
        <div class="space-y-6 text-gray-600">

            {{-- Summary --}}
            <section>
                <h3 class="font-semibold">
                    Summary
                </h3>

                <p class="mt-2 text-sm border border-gray-400 p-2.5">
                    {{ $result['summary'] }}
                </p>
            </section>

            {{-- Classification --}}
            <section>
                <h3 class="font-semibold">
                    Suggested Classification
                </h3>

                <div class="mt-3 grid grid-cols-2 gap-4">

                    <div class="rounded-md border p-4  border-gray-400">
                        <div class="text-xs text-gray-500">
                            Category
                        </div>

                        <div class="mt-1 font-medium">
                            {{ $result['suggested_category'] ?? 'No suggestion' }}
                        </div>
                    </div>

                    <div class="rounded-md border p-4 border-gray-400">
                        <div class="text-xs text-gray-500">
                            Priority
                        </div>

                        <div class="mt-1 font-medium">
                            {{ $result['suggested_priority'] ?? 'No suggestion' }}
                        </div>
                    </div>

                </div>
            </section>

            {{-- Next actions --}}
            <section>
                <h3 class="font-semibold">
                    Suggested Next Actions
                </h3>

                <ul class="mt-3 space-y-2 border-gray-400 border p-4">
                    @forelse ($result["suggested_next_actions"]
                        as $action)
                        <li class="rounded-md border p-3">
                            {{ $action }}
                        </li>
                    @empty
                        <li class="text-sm text-gray-500">
                            No next actions suggested.
                        </li>
                    @endforelse
                </ul>
            </section>

            {{-- Customer response --}}
            <section>
                <h3 class="font-semibold">
                    Customer Response Draft
                </h3>

                @if ($result['customer_response_draft'])
                    <div class="mt-3 rounded-md border p-4 border-gray-400">
                        <div class="whitespace-pre-line text-sm">
                            {{ $result['customer_response_draft'] }}
                        </div>
                    </div>

                    @can('ai.case_assistant.apply', $case)
                        <button type="button" class="mt-3 rounded-md border px-4 py-2 text-sm">
                            Use Draft
                        </button>
                    @endcan
                @else
                    <p class="mt-2 text-sm text-gray-500">
                        No response draft was generated.
                    </p>
                @endif
            </section>

            {{-- Warnings --}}
            @if (count($result['warnings']))
                <section>
                    <h3 class="font-semibold">
                        Warnings
                    </h3>

                    <ul class="mt-3 space-y-2 border-gray-400 border p-4">
                        @foreach ($result['warnings'] as $warning)
                            <li class="rounded-md border p-3 text-sm">
                                {{ $warning }}
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

        </div>
    @endif
</div>
