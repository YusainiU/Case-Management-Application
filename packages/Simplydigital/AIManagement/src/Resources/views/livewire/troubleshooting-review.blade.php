<section wire:poll.5s class="mt-6 space-y-4 border-t border-gray-300 pt-5 dark:border-gray-700" aria-label="AI troubleshooting review">
    <div>
        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Troubleshooting Review</h3>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
            Convert Case Assistant suggestions into an editable, staff-reviewed internal progress note.
            Suggestions are not proof that any diagnostic steps were performed. This does not resolve or close the case.
        </p>
    </div>

    @if ($notice)
        <p role="status" class="rounded-md border border-blue-300 p-3 text-sm">{{ $notice }}</p>
    @endif

    @if ($analysisCurrent)
        <p class="text-sm text-green-700 dark:text-green-300">
            Case Assistant analysis #{{ $analysis->id }} completed. Troubleshooting draft is ready.
        </p>
    @elseif ($analysis && in_array($analysis->status, ['pending', 'processing'], true))
        <p class="text-sm text-amber-700 dark:text-amber-300">
            Case Assistant re-analysis #{{ $analysis->id }} is {{ $analysis->status }}.
            This section refreshes automatically when it completes.
        </p>
    @elseif ($analysis?->status === 'failed')
        <p class="text-sm text-red-700 dark:text-red-300">
            The latest Case Assistant analysis failed. Run it again before preparing a troubleshooting note.
        </p>
    @elseif ($analysis?->status === 'completed')
        <p class="text-sm text-amber-700 dark:text-amber-300">
            Analysis #{{ $analysis->id }} was completed before the case was last updated.
            Re-analyse the case to prepare a current troubleshooting note.
        </p>
    @else
        <p class="text-sm text-amber-700 dark:text-amber-300">
            No completed Case Assistant analysis is available. Analyse the case first.
        </p>
    @endif

    @can('update', $case)
        @if (! $case->closed_datetime && ! $case->resolved_datetime)
            <button type="button" wire:click="prepare" wire:loading.attr="disabled" wire:target="prepare"
                    @disabled(! $analysisCurrent)
                    class="rounded-md border border-indigo-500 px-3 py-2 text-sm font-medium text-indigo-700 dark:text-indigo-300">
                Prepare editable troubleshooting note
            </button>

            @if ($sourceAnalysisId !== null)
                <div class="space-y-3 rounded-lg border border-gray-300 p-4 dark:border-gray-700">
                    <p class="text-sm font-medium">Review and edit — nothing has been recorded yet</p>
                    <label for="ai-troubleshooting-draft" class="block text-sm">Internal progress note</label>
                    <textarea id="ai-troubleshooting-draft" wire:model="draft" rows="14" maxlength="10000"
                              class="w-full rounded-md border border-gray-300 bg-white p-3 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"></textarea>
                    @error('draft') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                    <label class="flex items-start gap-2 text-sm">
                        <input type="checkbox" wire:model="confirmed" class="mt-1">
                        <span>I have reviewed the draft, removed unsupported claims, and distinguished proposed checks from work actually performed.</span>
                    </label>
                    @error('confirmed') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                    <button type="button" wire:click="save" wire:loading.attr="disabled"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white">
                        Save reviewed internal progress note
                    </button>
                </div>
            @endif
        @endif
    @endcan
</section>
