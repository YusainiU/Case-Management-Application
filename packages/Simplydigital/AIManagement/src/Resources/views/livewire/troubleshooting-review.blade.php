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
        @if ($analysisOlderThanCase)
            <p class="rounded-md border border-amber-300 p-3 text-sm text-amber-800 dark:border-amber-700 dark:text-amber-200">
                The case has changed since this analysis completed (for example, category, engineer or work status).
                You can still use the analysis without re-running it. Review the suggestions against the
                current case and remove anything outdated before saving.
            </p>
        @endif
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
            A completed analysis is available. Review its suggestions against the latest case details before use.
        </p>
    @else
        <p class="text-sm text-amber-700 dark:text-amber-300">
            No completed Case Assistant analysis is available. Analyse the case first.
        </p>
    @endif

    @can('update', $case)
        @can('case-assistant.analyze', $case)
            @if (! $case->closed_datetime && ! $case->resolved_datetime)
                <div class="space-y-3 rounded-lg border border-indigo-300 bg-indigo-50/50 p-4 dark:border-indigo-500/40 dark:bg-indigo-500/10">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Recommended solution (separate AI request)</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-200">
                        Ask the configured AI provider to suggest a possible fix, diagnostic checks,
                        verification steps and risks for this case. This is different from Analyze Case
                        and does not change the case or replace an engineer's judgement.
                    </p>
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">
                        Credit warning: clicking Recommend Solution makes a new AI provider request.
                        It may consume provider tokens/credits and incur a charge.
                    </p>
                    @if ($solutionConfigIssue)
                        <p class="text-sm text-red-700 dark:text-red-300">{{ $solutionConfigIssue }}</p>
                    @endif
                    @if ($analysisCurrent && ! in_array($solutionAnalysis?->status, ['pending', 'processing'], true))
                        <label class="flex items-start gap-2 text-sm">
                            <input type="checkbox" wire:model="confirmSolutionCredit" class="mt-1">
                            <span>I understand this is a separate, potentially chargeable AI provider request.</span>
                        </label>
                        @error('confirmSolutionCredit') <p class="text-sm text-red-700 dark:text-red-300">{{ $message }}</p> @enderror
                        <button type="button" wire:click="recommendSolution" wire:loading.attr="disabled"
                            wire:target="recommendSolution"
                            @disabled($solutionConfigIssue)
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-60">
                            <span wire:loading.remove wire:target="recommendSolution">Recommend Solution</span>
                            <span wire:loading wire:target="recommendSolution">Queuing solution request…</span>
                        </button>
                    @elseif (! $analysisCurrent)
                        <p class="text-sm text-amber-800 dark:text-amber-300">
                            Complete the initial Case Assistant analysis before requesting a dedicated solution.
                        </p>
                    @endif
                    @if ($solutionAnalysis)
                        <p class="text-sm font-medium" role="status">
                            Solution request #{{ $solutionAnalysis->id }}: {{ ucfirst($solutionAnalysis->status) }}
                        </p>
                        @if (in_array($solutionAnalysis->status, ['pending', 'processing'], true))
                            <div role="progressbar" aria-label="Solution recommendation running"
                                class="h-2 overflow-hidden rounded-full bg-indigo-100 dark:bg-indigo-950">
                                <div class="h-full w-1/2 animate-pulse rounded-full bg-indigo-600"></div>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-300">
                                Waiting for the queue and AI provider. Processing time is not predictable.
                            </p>
                        @elseif ($solutionAnalysis->status === 'failed')
                            <p class="text-sm text-red-700 dark:text-red-300">
                                {{ $solutionAnalysis->error ?? 'Solution research failed. Review logs before retrying.' }}
                            </p>
                        @elseif ($solutionAnalysis->status === 'completed' && is_array($solutionAnalysis->result))
                            @php $solution = $solutionAnalysis->result; @endphp
                            <div class="space-y-3 rounded-md border border-indigo-200 bg-white p-3 text-sm dark:border-indigo-500/30 dark:bg-gray-900">
                                <p class="font-semibold text-indigo-800 dark:text-indigo-200">AI suggestion only — not a verified diagnosis or completed fix</p>
                                <h5 class="font-semibold">Possible solution</h5>
                                <p class="whitespace-pre-line break-words">{{ $solution['recommended_solution'] ?? '' }}</p>
                                <h5 class="font-semibold">Why it may help</h5>
                                <p class="whitespace-pre-line break-words">{{ $solution['reasoning'] ?? '' }}</p>
                                @foreach ([
                                    'diagnostic_checks' => 'Checks to perform',
                                    'suggested_steps' => 'Possible repair steps (not performed)',
                                    'verification_steps' => 'Verification to perform',
                                    'risks_and_uncertainties' => 'Risks and uncertainties',
                                ] as $field => $heading)
                                    <h5 class="font-semibold">{{ $heading }}</h5>
                                    <ul class="list-disc space-y-1 pl-5">
                                        @forelse ($solution[$field] ?? [] as $step)
                                            <li class="whitespace-pre-line break-words">{{ $step }}</li>
                                        @empty
                                            <li>No specific items provided.</li>
                                        @endforelse
                                    </ul>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
            @endif
        @endcan
    @endcan

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
                        <span>I have checked these suggestions against the current case, removed outdated or unsupported claims,
                            and distinguished proposed checks from work actually performed.</span>
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
