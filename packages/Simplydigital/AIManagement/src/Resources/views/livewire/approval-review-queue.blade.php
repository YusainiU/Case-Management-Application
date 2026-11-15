<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 text-gray-800 dark:text-gray-100">
    <header class="space-y-2">
        <h1 class="text-2xl font-semibold">AI Approval Review Queue</h1>
        <p class="text-sm text-gray-600 dark:text-gray-300">
            Review category, engineer assignment, and Start Work proposals, decisions, and execution results.
        </p>
        <p class="text-sm text-gray-600 dark:text-gray-300">
            Category execution: <strong>{{ config('ai-management.approvals.execution_enabled', false) ? 'Enabled' : 'Disabled' }}</strong>
            · Engineer execution: <strong>{{ config('ai-management.engineer_assignment.execution_enabled', false) ? 'Enabled' : 'Disabled' }}</strong>
            · Start Work execution: <strong>{{ config('ai-management.work_start.execution_enabled', false) ? 'Enabled' : 'Disabled' }}</strong>
        </p>
    </header>

    @if ($notice)
        <p role="status" class="rounded-lg border border-blue-300 p-3 text-sm">{{ $notice }}</p>
    @endif

    <div class="flex flex-wrap items-center gap-3">
        <label for="ai-proposal-status" class="text-sm font-medium">Status</label>
        <select id="ai-proposal-status" wire:model.live="status"
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            @foreach ($statuses as $option)
                <option value="{{ $option }}">{{ ucfirst($option) }}</option>
            @endforeach
        </select>
        <span class="text-sm text-gray-500">{{ $proposals->total() }} matching proposals</span>
    </div>

    <div wire:poll.10s class="space-y-4">
        @forelse ($proposals as $proposal)
            <article wire:key="queue-proposal-{{ $proposal->id }}"
                     class="space-y-3 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="font-semibold">
                            Case {{ $proposal->case?->number ?? $proposal->case_id }}
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ $proposal->case?->short_description ?? 'Case details unavailable' }}
                        </p>
                    </div>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-800">
                        {{ ucfirst($proposal->status) }}
                    </span>
                </div>

                <p class="text-xs font-medium text-indigo-700 dark:text-indigo-300">
                    {{ match ($proposal->action_type) {
                        'assign_case_engineer' => 'Engineer assignment',
                        'start_case_work' => 'Start Work',
                        default => 'Category change',
                    } }}
                </p>
                <div class="grid gap-3 text-sm sm:grid-cols-2">
                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ match ($proposal->action_type) {
                                'assign_case_engineer' => 'Current assignee',
                                'start_case_work' => 'Current case state',
                                default => 'Current case category',
                            } }}
                        </div>
                        <div class="font-semibold">
                            {{ $proposal->action_type === 'assign_case_engineer'
                                ? ($proposal->case?->assignedToUser?->name ?? 'Unassigned')
                                : ($proposal->action_type === 'start_case_work'
                                    ? ($proposal->case?->state?->value ?? 'Unknown')
                                    : ($proposal->case?->category?->value ?? 'Unknown')) }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ match ($proposal->action_type) {
                                'assign_case_engineer' => 'Proposed engineer',
                                'start_case_work' => 'Proposed case state',
                                default => 'AI proposed category',
                            } }}
                        </div>
                        <div class="font-semibold">
                            {{ $proposal->action_type === 'assign_case_engineer'
                                ? ($engineerNames[$proposal->payload['engineer_user_id'] ?? 0] ?? 'Former engineer #'.($proposal->payload['engineer_user_id'] ?? '?'))
                                : ($proposal->action_type === 'start_case_work'
                                    ? ($proposal->payload['state'] ?? 'Unknown')
                                    : ($proposal->payload['category'] ?? 'Unknown')) }}
                        </div>
                    </div>
                </div>

                <p class="whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $proposal->reason }}</p>
                <p class="text-xs text-gray-500">
                    Requested {{ $proposal->created_at?->format('d M Y, H:i') }}
                    · Expires {{ $proposal->expires_at?->format('d M Y, H:i') }}
                    @if ($proposal->status === 'pending' && $proposal->expires_at?->isPast())
                        · Expired
                    @endif
                </p>

                @if ($proposal->approvals->isNotEmpty())
                    <section class="space-y-2 border-t border-gray-200 pt-3 dark:border-gray-700">
                        <h3 class="text-sm font-semibold">Decision history</h3>
                        @foreach ($proposal->approvals as $decision)
                            <div wire:key="approval-{{ $decision->id }}" class="text-sm text-gray-600 dark:text-gray-300">
                                <strong>{{ ucfirst($decision->decision) }}</strong>
                                by {{ $decision->reviewer?->name ?? 'Former user' }}
                                on {{ $decision->created_at?->format('d M Y, H:i') }}
                                @if ($decision->reason)
                                    <p class="mt-1 text-sm">Reason: {{ $decision->reason }}</p>
                                @endif
                            </div>
                        @endforeach
                    </section>
                @endif

                <div class="border-t border-gray-200 pt-3 text-sm dark:border-gray-700">
                    <strong>Execution:</strong>
                    {{ ucfirst($proposal->execution?->status ?? ($proposal->status === 'approved' ? 'Not recorded' : 'Not applicable')) }}
                    @if ($proposal->execution?->updated_at)
                        · Updated {{ $proposal->execution->updated_at->format('d M Y, H:i') }}
                    @endif
                    @if ($proposal->execution?->error)
                        <p class="mt-1 text-red-700 dark:text-red-400">{{ $proposal->execution->error }}</p>
                    @endif
                </div>

                @if ($proposal->status === 'pending' && ! $proposal->expires_at?->isPast() && $proposal->case)
                    @if (auth()->user()?->can(match ($proposal->action_type) {
                        'assign_case_engineer' => 'ai-engineer.approve',
                        'start_case_work' => 'ai-work-start.approve',
                        default => 'ai-category.approve',
                    }, $proposal->case))
                        <div class="space-y-2 border-t border-gray-200 pt-3 dark:border-gray-700">
                            <label for="queue-rejection-reason-{{ $proposal->id }}"
                                   class="block text-xs font-medium">Optional rejection reason</label>
                            <input type="text" id="queue-rejection-reason-{{ $proposal->id }}"
                                   wire:model="rejectionReason" maxlength="1000"
                                   class="w-full rounded-md border border-gray-300 p-2 text-sm dark:border-gray-700 dark:bg-gray-800">
                            @error('rejectionReason') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                            <div class="flex flex-wrap gap-2">
                                <button type="button" wire:click="approve('{{ $proposal->id }}')"
                                        wire:loading.attr="disabled"
                                        class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white">
                                    Approve
                                </button>
                                <button type="button" wire:click="reject('{{ $proposal->id }}')"
                                        wire:loading.attr="disabled"
                                        class="rounded-md border border-red-600 px-4 py-2 text-sm text-red-700">
                                    Reject
                                </button>
                            </div>
                        </div>
                    @endif
                @endif
            </article>
        @empty
            <p class="rounded-xl border border-gray-200 p-6 text-sm text-gray-500 dark:border-gray-700">
                No proposals match this filter.
            </p>
        @endforelse
    </div>

    {{ $proposals->links() }}
</div>
