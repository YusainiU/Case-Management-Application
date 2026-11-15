<section wire:poll.5s class="mt-6 space-y-4 border-t border-gray-300 pt-5 dark:border-gray-700"
         aria-label="AI category approval">
    <div>
        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">
            AI Category Recommendations
        </h3>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            AI suggestions never change a case until an authorised reviewer approves.
        </p>
    </div>

    @can('ai.proposals.view')
        <a href="{{ route('ai.approvals.index', ['status' => 'all'], false) }}" target="_blank" rel="noopener noreferrer"
           class="inline-block text-sm font-medium text-indigo-700 underline dark:text-indigo-300">
            View all AI approval requests and history
        </a>
    @endcan

    @if ($notice)
        <div role="status" class="rounded-md border border-blue-300 p-3 text-sm text-blue-800 dark:text-blue-200">
            {{ $notice }}
        </div>
    @endif

    @if ($analysis)
        <div class="rounded-md border border-gray-300 p-3 dark:border-gray-700">
            <p class="text-sm text-gray-700 dark:text-gray-300">
                Current category:
                <strong>{{ $case->category?->value ?? 'Uncategorised' }}</strong>
            </p>
            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                AI suggested:
                <strong>{{ $analysis->result['suggested_category'] ?? 'None' }}</strong>
            </p>
            @can('case-assistant.analyze', $case)
                <button type="button" wire:click="prepare" wire:loading.attr="disabled"
                        wire:target="prepare"
                        class="mt-3 rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white">
                    Prepare approval request
                </button>
            @endcan
        </div>
    @else
        <p class="text-sm text-gray-500">Run the Case Assistant analysis to get recommendations.</p>
    @endif

    @forelse ($proposals as $proposal)
        <article wire:key="ai-proposal-{{ $proposal->id }}"
                 class="rounded-md border border-gray-300 p-4 dark:border-gray-700">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <strong class="text-sm text-gray-800 dark:text-gray-100">
                    Change category to {{ $proposal->payload['category'] ?? 'Unknown' }}
                </strong>
                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-700">
                    {{ ucfirst($proposal->status) }}
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $proposal->reason }}</p>
            <p class="mt-1 text-xs text-gray-500">
                Requested {{ $proposal->created_at?->diffForHumans() }}
                · Expires {{ $proposal->expires_at?->format('d M Y, H:i') }}
            </p>
            @if ($proposal->approvals->isNotEmpty())
                <div class="mt-3 space-y-2 border-t border-gray-200 pt-3 dark:border-gray-700">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Approval history</h4>
                    @foreach ($proposal->approvals as $decision)
                        <div wire:key="case-approval-{{ $decision->id }}" class="text-sm text-gray-600 dark:text-gray-300">
                            <strong>{{ ucfirst($decision->decision) }}</strong>
                            by {{ $decision->reviewer?->name ?? 'Former user' }}
                            on {{ $decision->created_at?->format('d M Y, H:i') }}
                            @if ($decision->reason)
                                <p class="mt-1">Reason: {{ $decision->reason }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
            @if (in_array($proposal->status, ['approved', 'completed', 'stale', 'failed'], true))
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Execution: {{ ucfirst($proposal->execution?->status ?? 'Not recorded') }}
                    @if ($proposal->execution?->updated_at)
                        · {{ $proposal->execution->updated_at->format('d M Y, H:i') }}
                    @endif
                </p>
                @if ($proposal->execution?->error)
                    <p class="mt-1 text-xs text-red-700 dark:text-red-400">
                        {{ $proposal->execution->error }}
                    </p>
                @endif
            @endif
            @if ($proposal->status === 'pending')
                @can('ai-category.approve', $case)
                    <div class="mt-3 space-y-2">
                        <label class="block text-xs text-gray-600 dark:text-gray-300"
                               for="reject-reason-{{ $proposal->id }}">
                            Optional rejection reason
                        </label>
                        <input id="reject-reason-{{ $proposal->id }}" type="text"
                               wire:model="rejectionReason" maxlength="1000"
                               class="w-full rounded-md border border-gray-300 p-2 text-sm dark:bg-gray-800">
                        @error('rejectionReason')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="flex gap-2">
                            <button type="button" wire:click="approve('{{ $proposal->id }}')"
                                    wire:loading.attr="disabled"
                                    class="rounded-md bg-green-700 px-3 py-2 text-sm text-white">
                                Approve
                            </button>
                            <button type="button" wire:click="reject('{{ $proposal->id }}')"
                                    wire:loading.attr="disabled"
                                    class="rounded-md border border-red-600 px-3 py-2 text-sm text-red-700">
                                Reject
                            </button>
                        </div>
                    </div>
                @else
                    <p class="mt-2 text-xs text-amber-700">Awaiting an authorised manager's review.</p>
                @endcan
            @endif
        </article>
    @empty
        <p class="text-sm text-gray-500">No approval requests have been prepared for this case.</p>
    @endforelse
</section>
