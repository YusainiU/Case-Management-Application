<section wire:poll.10s class="mt-6 space-y-4 border-t border-gray-300 pt-5 dark:border-gray-700" aria-label="AI-assisted engineer assignment">
    <div>
        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Engineer Assignment Recommendations</h3>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Explainable matching using listed engineer skills and current open-case counts.
            It does not verify engineer calendars or availability. A manager must approve any proposed assignment.
        </p>
        <p class="mt-1 text-sm">Current assignee: <strong>{{ $case->assignedToUser?->name ?? 'Unassigned' }}</strong></p>
    </div>
    @if ($notice)
        <p role="status" class="rounded-md border border-blue-300 p-3 text-sm">{{ $notice }}</p>
    @endif

    @can('ai-engineer.propose', $case)
        @if (! $case->closed_datetime && $recommendations)
            <div class="space-y-2 rounded-lg border border-gray-300 p-3 dark:border-gray-700">
                <label for="engineer-choice-{{ $case->id }}" class="block text-sm font-medium">Suggested engineers (skill match, then workload)</label>
                <select wire:model="selectedEngineerId" id="engineer-choice-{{ $case->id }}"
                        class="w-full rounded-md border border-gray-300 p-2 text-sm dark:bg-gray-800">
                    <option value="">Select an engineer for human review</option>
                    @foreach ($recommendations as $candidate)
                        <option value="{{ $candidate['user_id'] }}" @disabled($candidate['user_id'] === $case->assigned_to)>
                            {{ $candidate['name'] }} · {{ count($candidate['matched_skills']) }} matched skills · {{ $candidate['open_cases'] }} open cases
                            {{ $candidate['evidence'] === 'workload_only' ? '(skills unverified)' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('selectedEngineerId') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                @foreach (array_slice($recommendations, 0, 5) as $candidate)
                    <p class="text-xs text-gray-600 dark:text-gray-300">
                        {{ $candidate['name'] }}:
                        {{ $candidate['matched_skills'] ? 'Matched '.implode(', ', $candidate['matched_skills']) : 'No confirmed skill match' }}
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
            @if ($proposal->status === 'approved' || $proposal->status === 'completed')
                <p class="text-xs text-gray-500">Execution: {{ $proposal->execution?->status ?? 'Not recorded' }}</p>
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
