<section wire:poll.10s class="mt-6 space-y-4 border-t border-gray-300 pt-5 dark:border-gray-700" aria-label="Start Work approval">
    <h3 class="text-base font-semibold">Start Work (human approval)</h3>
    <p class="text-sm text-gray-600 dark:text-gray-300">
        Suggest moving an assigned, unresolved case from Open to In Progress.
        Confirm that work has actually begun before approving.
        This rule does not require an AI provider and does not modify SLA, resolution or closure records.
    </p>
    <p class="text-sm">Current state: <strong>{{ $case->state?->value ?? 'Unknown' }}</strong>
        · Assigned engineer: <strong>{{ $case->assignedToUser?->name ?? 'Unassigned' }}</strong></p>
    @if ($notice)
        <p role="status" class="rounded-md border border-blue-300 p-3 text-sm">{{ $notice }}</p>
    @endif
    @can('ai-work-start.propose', $case)
        @if ($case->state === \Simplydigital\CaseManagement\Enums\CaseState::Open && $case->assigned_to
            && ! $case->closed_datetime && ! $case->resolved_datetime)
            <button type="button" wire:click="prepare" wire:loading.attr="disabled" wire:target="prepare"
                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white">
                Prepare Start Work approval
            </button>
        @endif
    @endcan
    @forelse ($proposals as $proposal)
        <article wire:key="start-work-{{ $proposal->id }}" class="space-y-2 rounded-lg border border-gray-300 p-3 dark:border-gray-700">
            <div class="flex items-center justify-between gap-2">
                <strong class="text-sm">Move to In Progress</strong>
                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-800">{{ ucfirst($proposal->status) }}</span>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $proposal->reason }}</p>
            @foreach ($proposal->approvals as $decision)
                <p class="text-xs text-gray-500">
                    {{ ucfirst($decision->decision) }} by {{ $decision->reviewer?->name ?? 'Former user' }}
                    · {{ $decision->created_at?->format('d M Y, H:i') }}
                    @if ($decision->reason) · {{ $decision->reason }} @endif
                </p>
            @endforeach
            @if (in_array($proposal->status, ['approved', 'completed', 'stale', 'failed'], true))
                <p class="text-xs text-gray-500">Execution: {{ ucfirst($proposal->execution?->status ?? 'Not recorded') }}
                    @if ($proposal->execution?->updated_at)
                        · {{ $proposal->execution->updated_at->format('d M Y, H:i') }}
                    @endif
                </p>
                @if ($proposal->execution?->error)
                    <p class="text-xs text-red-700 dark:text-red-400">{{ $proposal->execution->error }}</p>
                @endif
            @endif
            @if ($proposal->status === 'pending' && $proposal->expires_at?->isFuture())
                @can('ai-work-start.approve', $case)
                    <label for="start-work-reject-{{ $proposal->id }}" class="block text-xs">Optional rejection reason</label>
                    <input id="start-work-reject-{{ $proposal->id }}" maxlength="1000"
                        wire:model="rejectionReason" class="w-full rounded-md border border-gray-300 p-2 text-sm dark:bg-gray-800">
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
        <p class="text-xs text-gray-500">No Start Work proposals yet.</p>
    @endforelse
</section>
