<section wire:poll.10s class="mt-6 space-y-4 border-t border-gray-300 pt-5 dark:border-gray-700" aria-label="AI-assisted engineer assignment">
    <div>
        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Engineer Assignment Recommendations</h3>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Ranking: verified exact and AI-suggested skill matches first (exact matches win ties),
            then active unexpired certifications matching the case text, then fewer open cases. This does not verify engineer schedules, availability or capacity.
            A manager must approve every proposed assignment.
        </p>
        <p class="mt-1 text-sm">Current assignee: <strong>{{ $case->assignedToUser?->name ?? 'Unassigned' }}</strong></p>
    </div>
    @if ($notice)
        <p role="status" class="rounded-md border border-blue-300 p-3 text-sm">{{ $notice }}</p>
    @endif

    @can('ai-engineer.propose', $case)
        @if (config('ai-management.engineer_semantic.enabled', false) && ! $case->closed_datetime)
            <div class="space-y-2 rounded-md border border-gray-300 p-3 dark:border-gray-700">
                <p class="text-sm">Optional AI semantic matching compares this case's issue text with registered skill names.
                    Case text and skill names are sent to the configured AI provider, but engineer names are not.
                    AI suggestions must cite an exact phrase from the case and are not a diagnosis.</p>
                <button type="button" wire:click="requestSemanticMatch" wire:loading.attr="disabled"
                        wire:target="requestSemanticMatch"
                        class="rounded-md border border-indigo-500 px-3 py-2 text-sm font-medium text-indigo-700 dark:text-indigo-300">
                    Analyse skill relevance with AI
                </button>
                @if ($semanticAnalysis)
                    <p class="text-xs text-gray-600 dark:text-gray-300">
                        Semantic analysis: {{ ucfirst($semanticAnalysis->status) }}
                        @if ($semanticAnalysis->status === 'completed')
                            · {{ collect($recommendations)->sum(fn ($engineer) => count($engineer['semantic_matches'])) }} engineer-skill matches
                            (valid only if case and skill catalogue are unchanged)
                        @elseif ($semanticAnalysis->status === 'failed')
                            · {{ $semanticAnalysis->error ?? 'Exact matching remains available.' }}
                        @endif
                    </p>
                @endif
            </div>
        @endif
        @if (! $case->closed_datetime && $recommendations)
            <div class="space-y-2 rounded-lg border border-gray-300 p-3 dark:border-gray-700">
                <label for="engineer-choice-{{ $case->id }}" class="block text-sm font-medium">Suggested engineers (exact or evidence-based semantic skills, certification match, then workload)</label>
                <select wire:model="selectedEngineerId" id="engineer-choice-{{ $case->id }}"
                        class="w-full rounded-md border border-gray-300 p-2 text-sm dark:bg-gray-800">
                    <option value="">Select an engineer for human review</option>
                    @foreach ($recommendations as $candidate)
                        <option value="{{ $candidate['user_id'] }}" @disabled($candidate['user_id'] === $case->assigned_to)>
                            {{ $candidate['name'] }} · {{ count($candidate['matched_skills']) }} exact skills
                            · {{ count($candidate['semantic_matches']) }} semantic skills
                            · {{ count($candidate['matched_certifications']) }} certification matches
                            · {{ $candidate['open_cases'] }} open cases
                            {{ $candidate['evidence'] === 'workload_only' ? '(no confirmed match)' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('selectedEngineerId') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                @foreach (array_slice($recommendations, 0, 5) as $candidate)
                    <p class="text-xs text-gray-600 dark:text-gray-300">
                        {{ $candidate['name'] }}:
                        {{ $candidate['matched_skills'] ? 'Exact skills: '.implode(', ', $candidate['matched_skills']) : 'No exact skill match' }}
                        @foreach ($candidate['semantic_matches'] as $match)
                            <span class="block pl-3">AI skill: {{ $match['skill'] }} ({{ $match['confidence'] }})
                                · Case evidence: "{{ $match['case_evidence'] }}"
                                · {{ $match['reason'] }}</span>
                        @endforeach
                        · {{ $candidate['matched_certifications'] ? 'Valid certifications: '.implode(', ', $candidate['matched_certifications']) : 'No valid certification match' }}
                        · {{ $candidate['open_cases'] }} open cases
                    </p>
                @endforeach
                <button type="button" wire:click="prepare" wire:loading.attr="disabled" wire:target="prepare"
                        class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white">
                    Prepare engineer assignment approval
                </button>
            </div>
        @else
            <p class="text-sm text-gray-500">No eligible engineers were found, or this case is closed.</p>
        @endif
    @endcan

    @forelse ($proposals as $proposal)
        <article wire:key="engineer-approval-{{ $proposal->id }}" class="space-y-2 rounded-lg border border-gray-300 p-3 dark:border-gray-700">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <strong class="text-sm">Assign to {{ $recommendations ? (collect($recommendations)->firstWhere('user_id', $proposal->payload['engineer_user_id'] ?? null)['name'] ?? 'Engineer #'.($proposal->payload['engineer_user_id'] ?? '?')) : 'Engineer #'.($proposal->payload['engineer_user_id'] ?? '?') }}</strong>
                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-800">{{ ucfirst($proposal->status) }}</span>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $proposal->reason }}</p>
            @foreach ($proposal->approvals as $decision)
                <p class="text-xs text-gray-500">
                    {{ ucfirst($decision->decision) }} by {{ $decision->reviewer?->name ?? 'Former user' }}
                    on {{ $decision->created_at?->format('d M Y, H:i') }}
                    @if ($decision->reason) · {{ $decision->reason }} @endif
                </p>
            @endforeach
            @if (in_array($proposal->status, ['approved', 'completed', 'stale', 'failed'], true))
                <p class="text-xs text-gray-500">Execution: {{ ucfirst($proposal->execution?->status ?? 'Not recorded') }}
                    @if ($proposal->execution?->updated_at)
                        · {{ $proposal->execution->updated_at->format('d M Y, H:i') }}
                    @endif</p>
                @if ($proposal->execution?->error)
                    <p class="text-xs text-red-700 dark:text-red-400">{{ $proposal->execution->error }}</p>
                @endif
            @endif
            @if ($proposal->status === 'pending')
                @can('ai-engineer.approve', $case)
                    <label for="engineer-reject-{{ $proposal->id }}" class="block text-xs">Optional rejection reason</label>
                    <input id="engineer-reject-{{ $proposal->id }}" wire:model="rejectionReason" maxlength="1000"
                           class="w-full rounded-md border border-gray-300 p-2 text-sm dark:bg-gray-800">
                    <div class="flex gap-2">
                        <button type="button" wire:click="approve('{{ $proposal->id }}')" wire:loading.attr="disabled"
                                class="rounded-md bg-green-700 px-3 py-2 text-sm text-white">Approve</button>
                        <button type="button" wire:click="reject('{{ $proposal->id }}')" wire:loading.attr="disabled"
                                class="rounded-md border border-red-600 px-3 py-2 text-sm text-red-700">Reject</button>
                    </div>
                @endcan
            @endif
        </article>
    @empty
        <p class="text-xs text-gray-500">No engineer assignment proposals yet.</p>
    @endforelse
</section>
