<div class="min-h-screen bg-gray-50 text-slate-900">
    <main class="mx-auto w-full max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-indigo-700">Expenses</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">
                    {{ $case ? 'Case expenses - ' . $case->number : 'My expense claims' }}
                </h1>
                @if ($case)
                    <p class="mt-2 text-sm text-slate-600">Claims are permanently linked to this case.</p>
                @else
                    <p class="mt-2 text-sm text-slate-600">Your claims are grouped by the ISO week and year selected on
                        each claim.</p>
                @endif
            </div>

            @if ($canViewClaims)
                <div class="rounded-xl border border-emerald-200 bg-white px-4 py-3 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-800">Total amounts claimed</p>
                    <p class="mt-1 text-xl font-semibold text-emerald-900">£{{ number_format($totalClaimed, 2) }}</p>
                </div>
            @endif
        </header>

        @if ($message)
            <div role="status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                {{ $message }}
            </div>
        @endif

        @if ($case && $canCreate && !$showForm)
            <button type="button" wire:click="startNewExpense"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Add expense claim
            </button>
        @endif

        @if ($showForm && $canCreate)
            <section class="space-y-7 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <header>
                    <h2 class="text-base font-semibold text-gray-900">
                        @if ($editingExpenseId)
                            {{ $expensesByWeek->flatten()->firstWhere('id', $editingExpenseId)?->status === \Simplydigital\Expenses\Enums\ExpenseStatus::Rejected ? 'Amend and reapply claim' : 'Update expense claim' }}
                        @else
                            New expense claim
                        @endif
                    </h2>
                    @if ($case)
                        <p class="mt-1 text-sm text-gray-600">Case {{ $case->number }}</p>
                    @endif
                </header>

                <form wire:submit="saveExpense" enctype="multipart/form-data" class="space-y-7">
                    <section>
                        <div class="mb-5 flex items-start gap-3">
                            <div
                                class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700">
                                <flux:icon name="clipboard-document-list" class="size-5" />
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">Task details</h3>
                                <p class="mt-1 text-sm text-gray-600">Select the claim week and record when the work
                                    took place.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-x-5 gap-y-5 md:grid-cols-2 xl:grid-cols-3">
                            <div>
                                <label for="expenseWeekNumber" class="mb-2 block text-sm font-medium text-gray-700">Week
                                    of year</label>
                                <x-casemanagement::form-elements.input-text id="expenseWeekNumber" type="number"
                                    model="weekNumber" min="1" max="53" :required="true" />
                                <x-casemanagement::form-elements.input-error field="weekNumber" />
                            </div>

                            <div>
                                <label for="expenseWeekYear" class="mb-2 block text-sm font-medium text-gray-700">Week
                                    year</label>
                                <x-casemanagement::form-elements.input-text id="expenseWeekYear" type="number"
                                    model="weekYear" min="1900" max="9999" :required="true" />
                                <x-casemanagement::form-elements.input-error field="weekYear" />
                            </div>

                            <div>
                                <label for="expenseTaskDate" class="mb-2 block text-sm font-medium text-gray-700">Task
                                    date</label>
                                <x-casemanagement::form-elements.input-text id="expenseTaskDate" type="date"
                                    model="taskDate" class="scheme-light" :required="true" />
                                <x-casemanagement::form-elements.input-error field="taskDate" />
                            </div>

                            <div>
                                <label for="expenseStartTime" class="mb-2 block text-sm font-medium text-gray-700">Task
                                    started</label>
                                <x-casemanagement::form-elements.input-text id="expenseStartTime" type="time"
                                    model="startTime" class="scheme-light" :required="true" />
                                <x-casemanagement::form-elements.input-error field="startTime" />
                            </div>

                            <div>
                                <label for="expenseEndTime" class="mb-2 block text-sm font-medium text-gray-700">Task
                                    ended</label>
                                <x-casemanagement::form-elements.input-text id="expenseEndTime" type="time"
                                    model="endTime" class="scheme-light" :required="true" />
                                <x-casemanagement::form-elements.input-error field="endTime" />
                            </div>
                        </div>
                    </section>

                    <div class="border-t border-gray-200"></div>

                    <section>
                        <div class="mb-5 flex items-start gap-3">
                            <div
                                class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-700">
                                <flux:icon name="user-circle" class="size-5" />
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">Claim details</h3>
                                <p class="mt-1 text-sm text-gray-600">Describe the expense and enter the amount you are
                                    claiming.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-x-5 gap-y-5 md:grid-cols-2 xl:grid-cols-3">
                            <div>
                                <label for="expenseType" class="mb-2 block text-sm font-medium text-gray-700">Claim
                                    type</label>
                                <x-casemanagement::form-elements.select name="expenseType" model="type"
                                    placeholder="Select a claim type" :options="$expenseTypeOptions" :required="true" />
                                <x-casemanagement::form-elements.input-error field="type" />
                            </div>

                            <div>
                                <label for="expenseAmount" class="mb-2 block text-sm font-medium text-gray-700">Amount
                                    claimed (£)</label>
                                <x-casemanagement::form-elements.input-text id="expenseAmount" type="number"
                                    model="amount" min="0.01" steps="0.01" :required="true" />
                                <x-casemanagement::form-elements.input-error field="amount" />
                            </div>

                            <div class="md:col-span-2 xl:col-span-3">
                                <label for="expenseDescription"
                                    class="mb-2 block text-sm font-medium text-gray-700">Nature of claim</label>
                                <x-casemanagement::form-elements.textarea id="expenseDescription" model="description"
                                    rows="3" :required="true" />
                                <x-casemanagement::form-elements.input-error field="description" />
                            </div>
                        </div>
                    </section>

                    <div class="border-t border-gray-200"></div>

                    <section>
                        <div class="mb-5 flex items-start gap-3">
                            <div
                                class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-violet-100 text-violet-700">
                                <flux:icon name="document-text" class="size-5" />
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">Receipt</h3>
                                <p class="mt-1 text-sm text-gray-600">Attach an image of your receipt if you have one.
                                </p>
                            </div>
                        </div>

                        <div>
                            <label for="expenseReceipt" class="mb-2 block text-sm font-medium text-gray-700">Receipt
                                image (optional)</label>
                            <input id="expenseReceipt" type="file" accept="image/jpeg,image/png,image/webp"
                                wire:model="receipt"
                                class="block w-full rounded-xl border border-gray-300 bg-white px-3.5 py-3 text-sm text-gray-900 shadow-sm transition file:me-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-800 hover:file:bg-gray-200" />
                            <x-casemanagement::form-elements.input-error field="receipt" />
                            @if ($receipt)
                                <p class="mt-2 text-xs leading-5 text-gray-600">Selected:
                                    {{ $receipt->getClientOriginalName() }}</p>
                            @elseif ($editingExpenseId && $expensesByWeek->flatten()->firstWhere('id', $editingExpenseId)?->receipt_path)
                                <p class="mt-2 text-xs leading-5 text-gray-600">A receipt is attached. Upload a new
                                    image to replace it.</p>
                            @endif
                        </div>
                    </section>

                    @if ($editingExpenseId && $expensesByWeek->flatten()->firstWhere('id', $editingExpenseId)?->rejection_reason)
                        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                            <span class="font-semibold">Manager's reason:</span>
                            {{ $expensesByWeek->flatten()->firstWhere('id', $editingExpenseId)->rejection_reason }}
                        </div>
                    @endif

                    <div class="flex flex-wrap justify-end gap-3 border-t border-gray-200 pt-5">
                        <button type="button" wire:click="cancelEditing"
                            class="cursor-pointer rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="cursor-pointer rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-60">
                            @if (
                                $editingExpenseId &&
                                    $expensesByWeek->flatten()->firstWhere('id', $editingExpenseId)?->status ===
                                        \Simplydigital\Expenses\Enums\ExpenseStatus::Rejected)
                                Reapply claim
                            @elseif ($editingExpenseId)
                                Save changes
                            @else
                                Submit claim
                            @endif
                        </button>
                    </div>
                </form>
            </section>
        @endif

        @if ($canViewClaims)
            <section class="space-y-5">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Your claims</h2>
                </div>

                @forelse ($expensesByWeek as $week => $expenses)
                    <section wire:key="expense-week-{{ $week }}"
                        class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                        <h3 class="border-b border-gray-200 bg-gray-100 px-4 py-3 text-sm font-semibold text-gray-900">
                            {{ $week }}</h3>
                        <div class="divide-y divide-gray-100">
                            @foreach ($expenses as $expense)
                                <article wire:key="expense-claim-{{ $expense->id }}"
                                    class="flex flex-wrap items-start justify-between gap-4 p-4">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span @class([
                                                'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                                'bg-amber-100 text-amber-800' => in_array(
                                                    $expense->status,
                                                    [
                                                        \Simplydigital\Expenses\Enums\ExpenseStatus::AwaitingApproval,
                                                        \Simplydigital\Expenses\Enums\ExpenseStatus::RejectedAndReapply,
                                                    ],
                                                    true),
                                                'bg-emerald-100 text-emerald-800' =>
                                                    $expense->status ===
                                                    \Simplydigital\Expenses\Enums\ExpenseStatus::Approved,
                                                'bg-rose-100 text-rose-800' =>
                                                    $expense->status ===
                                                    \Simplydigital\Expenses\Enums\ExpenseStatus::Rejected,
                                            ])>{{ $expense->status->label() }}</span>
                                            <span class="text-xs text-slate-600">{{ $expense->case?->number }}</span>
                                        </div>
                                        <p class="mt-2 text-sm font-semibold text-slate-900">
                                            {{ $expense->type->label() }} · {{ $expense->task_date->format('d M Y') }}
                                        </p>
                                        <p class="mt-1 whitespace-pre-line text-sm text-slate-700">
                                            {{ $expense->description }}</p>
                                        <p class="mt-1 text-xs text-slate-600">
                                            {{ substr((string) $expense->start_time, 0, 5) }}–{{ substr((string) $expense->end_time, 0, 5) }}
                                        </p>
                                        @if ($expense->rejection_reason)
                                            <p
                                                class="mt-2 rounded-md border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-900">
                                                <span class="font-semibold">Reason:</span>
                                                {{ $expense->rejection_reason }}</p>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <p class="whitespace-nowrap text-sm font-semibold text-slate-900">
                                            £{{ number_format((float) $expense->amount, 2) }}</p>
                                        @if ($expense->receipt_path)
                                            <button type="button" wire:click="downloadReceipt({{ $expense->id }})"
                                                class="text-sm font-medium text-indigo-700 underline-offset-2 hover:text-indigo-900 hover:underline">Receipt</button>
                                        @endif
                                        @if (auth()->user()->can('expense.update') &&
                                                in_array(
                                                    $expense->status,
                                                    [
                                                        \Simplydigital\Expenses\Enums\ExpenseStatus::AwaitingApproval,
                                                        \Simplydigital\Expenses\Enums\ExpenseStatus::Rejected,
                                                    ],
                                                    true))
                                            <button type="button" wire:click="editExpense({{ $expense->id }})"
                                                class="text-sm font-medium text-indigo-700 underline-offset-2 hover:text-indigo-900 hover:underline">
                                                {{ $expense->status === \Simplydigital\Expenses\Enums\ExpenseStatus::Rejected ? 'Amend / reapply' : 'Edit' }}
                                            </button>
                                        @endif
                                        @if (auth()->user()->can('expense.delete') &&
                                                $expense->status === \Simplydigital\Expenses\Enums\ExpenseStatus::AwaitingApproval)
                                            <button type="button" wire:click="deleteExpense({{ $expense->id }})"
                                                wire:confirm="Delete this expense claim?"
                                                class="text-sm font-medium text-rose-600 hover:text-rose-800">Delete</button>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <div
                        class="rounded-xl border border-dashed border-gray-300 bg-white px-4 py-10 text-center text-sm text-gray-700 shadow-sm">
                        No expense claims have been submitted{{ $case ? ' for this case' : '' }}.
    </main>
</div>
@endforelse
</section>
@endif
</div>
