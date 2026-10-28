<div class="min-h-screen bg-gray-50 text-slate-900">
<main class="mx-auto w-full max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
    <header>
        <p class="text-sm font-semibold uppercase tracking-wide text-indigo-700">Expenses</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">Expense approvals</h1>
        <p class="mt-2 text-sm text-slate-600">Review claims, inspect receipts and export claims for a selected ISO week.</p>
    </header>

    @if ($message)
        <div role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            {{ $message }}
        </div>
    @endif

    <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="mb-3">
            <h2 class="text-base font-semibold text-slate-900">Weekly report</h2>
            <p class="mt-1 text-sm text-slate-600">Download all claims recorded for an ISO week as a spreadsheet-compatible CSV.</p>
        </div>
        <form wire:submit="downloadReport" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="reportWeek" class="mb-1 block text-sm font-medium text-slate-700">Week</label>
                <input id="reportWeek" type="number" min="1" max="53" wire:model="reportWeek" required class="block w-28 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" />
                @error('reportWeek') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="reportYear" class="mb-1 block text-sm font-medium text-slate-700">Year</label>
                <input id="reportYear" type="number" min="1900" max="9999" wire:model="reportYear" required class="block w-32 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" />
                @error('reportYear') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Download report</button>
        </form>
    </section>

    <nav class="flex flex-wrap gap-2" aria-label="Expense status">
        @foreach ([
            'awaiting_approval' => 'Awaiting approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ] as $tab => $label)
            <button
                type="button"
                wire:click="selectTab('{{ $tab }}')"
                aria-pressed="{{ $activeTab === $tab ? 'true' : 'false' }}"
                @class([
                    'rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                    'bg-indigo-600 text-white' => $activeTab === $tab,
                    'bg-white text-slate-700 ring-1 ring-gray-300 hover:bg-gray-100' => $activeTab !== $tab,
                ])
            >
                {{ $label }}
            </button>
        @endforeach
    </nav>

    @forelse ($expensesByWeek as $week => $expenses)
        <section wire:key="admin-expense-week-{{ $week }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <h2 class="border-b border-gray-200 bg-gray-100 px-4 py-3 text-sm font-semibold text-gray-900">{{ $week }}</h2>
            <div class="divide-y divide-gray-100">
                @foreach ($expenses as $expense)
                    <article wire:key="admin-expense-{{ $expense->id }}" class="p-4 sm:p-5">
                        <button type="button" wire:click="selectExpense({{ $expense->id }})" class="flex w-full flex-wrap items-start justify-between gap-4 rounded-lg p-2 text-left transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                        'bg-amber-100 text-amber-800' => in_array($expense->status, [\Simplydigital\Expenses\Enums\ExpenseStatus::AwaitingApproval, \Simplydigital\Expenses\Enums\ExpenseStatus::RejectedAndReapply], true),
                                        'bg-emerald-100 text-emerald-800' => $expense->status === \Simplydigital\Expenses\Enums\ExpenseStatus::Approved,
                                        'bg-rose-100 text-rose-800' => $expense->status === \Simplydigital\Expenses\Enums\ExpenseStatus::Rejected,
                                    ])>{{ $expense->status->label() }}</span>
                                    <span class="text-xs text-slate-600">Case {{ $expense->case?->number }}</span>
                                </div>
                                <h3 class="mt-2 text-sm font-semibold text-slate-900">{{ $expense->claimant?->name ?? 'Former user' }} · {{ $expense->type->label() }}</h3>
                                <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $expense->description }}</p>
                                <p class="mt-1 text-xs text-slate-600">{{ $expense->task_date->format('d M Y') }} · {{ substr((string) $expense->start_time, 0, 5) }}–{{ substr((string) $expense->end_time, 0, 5) }}</p>
                            </div>
                            <span class="whitespace-nowrap text-sm font-semibold text-slate-900">£{{ number_format((float) $expense->amount, 2) }}</span>
                        </button>

                        @if ($selectedExpense?->id === $expense->id)
                            <div class="mt-4 space-y-4 rounded-lg border border-indigo-200 bg-indigo-50 p-4">
                                <dl class="grid gap-3 text-sm sm:grid-cols-2">
                                    <div>
                                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-600">Claimant</dt>
                                        <dd class="mt-1 text-slate-900">{{ $expense->claimant?->name ?? 'Former user' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-600">Case</dt>
                                        <dd class="mt-1 text-slate-900">{{ $expense->case?->number ?? 'Unavailable' }}</dd>
                                    </div>
                                    @if ($expense->reviewer)
                                        <div>
                                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-600">Last reviewed by</dt>
                                            <dd class="mt-1 text-slate-900">{{ $expense->reviewer->name }} · {{ $expense->reviewed_at?->format('d M Y H:i') }}</dd>
                                        </div>
                                    @endif
                                </dl>

                                @if ($expense->rejection_reason)
                                    <p class="whitespace-pre-line rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-900"><span class="font-semibold">Rejection reason:</span> {{ $expense->rejection_reason }}</p>
                                @endif

                                @if ($expense->receipt_path)
                                    <button type="button" wire:click="downloadReceipt({{ $expense->id }})" class="text-sm font-medium text-indigo-700 underline underline-offset-2 hover:text-indigo-900">Download receipt</button>
                                @endif

                                @if ($expense->status->canBeReviewed() && $expense->user_id !== auth()->id())
                                    <div class="flex flex-wrap items-end gap-3 border-t border-indigo-200 pt-4">
                                        <div class="min-w-64 flex-1">
                                            <label for="rejectionReason" class="mb-1 block text-sm font-medium text-slate-700">Reason if rejecting</label>
                                            <textarea id="rejectionReason" wire:model="rejectionReason" rows="2" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"></textarea>
                                            @error('rejectionReason') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                                        </div>
                                        <button type="button" wire:click="approveExpense({{ $expense->id }})" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">Approve</button>
                                        <button type="button" wire:click="rejectExpense({{ $expense->id }})" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">Reject</button>
                                    </div>
                                @elseif ($expense->status->canBeReviewed())
                                    <p class="border-t border-indigo-200 pt-4 text-sm text-slate-700">You cannot review your own expense claim.</p>
                                @endif
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @empty
        <div class="rounded-xl border border-dashed border-gray-300 bg-white px-4 py-10 text-center text-sm text-gray-700 shadow-sm">
            No {{ $activeTab === 'awaiting_approval' ? 'claims awaiting approval' : $activeTab.' claims' }}.
        </div>
    @endforelse
</main>
</div>
