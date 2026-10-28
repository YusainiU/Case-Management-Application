<?php

namespace Simplydigital\Expenses\Livewire;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Simplydigital\Expenses\Enums\ExpenseStatus;
use Simplydigital\Expenses\Models\Expense;
use Simplydigital\Expenses\Services\ExpenseWorkflow;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('components.layouts.app')]
class ExpenseAdmin extends Component
{
    #[Locked]
    public string $activeTab = 'awaiting_approval';

    #[Locked]
    public ?int $selectedExpenseId = null;

    public string $rejectionReason = '';

    public string $reportWeek = '';

    public string $reportYear = '';

    public ?string $message = null;

    public function mount(): void
    {
        $this->authorize('manageAny', Expense::class);

        $today = now();
        $this->reportWeek = (string) $today->isoWeek();
        $this->reportYear = (string) $today->isoWeekYear();
    }

    public function selectTab(string $tab): void
    {
        if (! in_array($tab, ['awaiting_approval', 'approved', 'rejected'], true)) {
            abort(404);
        }

        $this->activeTab = $tab;
        $this->selectedExpenseId = null;
        $this->rejectionReason = '';
    }

    public function selectExpense(int $expenseId): void
    {
        $expense = Expense::query()->findOrFail($expenseId);
        $this->authorize('view', $expense);

        $this->selectedExpenseId = $expense->id;
        $this->rejectionReason = '';
    }

    public function approveExpense(int $expenseId, ExpenseWorkflow $workflow): void
    {
        $expense = Expense::query()->findOrFail($expenseId);
        $this->authorize('review', $expense);

        $workflow->review(
            $expense,
            $this->authenticatedUser(),
            ExpenseStatus::Approved,
        );

        $this->message = 'Expense claim approved.';
        $this->selectedExpenseId = null;
    }

    public function rejectExpense(int $expenseId, ExpenseWorkflow $workflow): void
    {
        $expense = Expense::query()->findOrFail($expenseId);
        $this->authorize('review', $expense);
        $this->validate([
            'rejectionReason' => ['required', 'string', 'max:2000'],
        ]);

        $workflow->review(
            $expense,
            $this->authenticatedUser(),
            ExpenseStatus::Rejected,
            trim($this->rejectionReason),
        );

        $this->message = 'Expense claim rejected.';
        $this->selectedExpenseId = null;
        $this->rejectionReason = '';
    }

    public function downloadReceipt(int $expenseId): StreamedResponse
    {
        $expense = Expense::query()->findOrFail($expenseId);
        $this->authorize('view', $expense);

        abort_unless(
            $expense->receipt_path
                && Storage::disk('local')->exists($expense->receipt_path),
            404,
        );

        return Storage::disk('local')->download(
            $expense->receipt_path,
            'expense-receipt-'.$expense->id.'.'.pathinfo($expense->receipt_path, PATHINFO_EXTENSION),
        );
    }

    public function downloadReport(): StreamedResponse
    {
        $this->authorize('manageAny', Expense::class);

        $validated = $this->validate([
            'reportWeek' => ['required', 'integer', 'between:1,53'],
            'reportYear' => ['required', 'integer', 'between:1900,9999'],
        ]);

        $weekStart = CarbonImmutable::now()->setISODate(
            (int) $validated['reportYear'],
            (int) $validated['reportWeek'],
        );

        if (
            $weekStart->isoWeekYear() !== (int) $validated['reportYear']
            || $weekStart->isoWeek() !== (int) $validated['reportWeek']
        ) {
            throw ValidationException::withMessages([
                'reportWeek' => 'The selected year does not contain that ISO week.',
            ]);
        }

        $weekNumber = (int) $validated['reportWeek'];
        $weekYear = (int) $validated['reportYear'];

        return response()->streamDownload(function () use ($weekNumber, $weekYear): void {
            $stream = fopen('php://output', 'wb');

            if ($stream === false) {
                throw new \RuntimeException('The expense report could not be generated.');
            }

            try {
                fputcsv($stream, [
                    'Expense ID',
                    'Claimant',
                    'Case',
                    'Week',
                    'Year',
                    'Task date',
                    'Start time',
                    'End time',
                    'Type',
                    'Description',
                    'Amount (GBP)',
                    'Status',
                    'Rejection reason',
                ], ',', '"', '');

                Expense::query()
                    ->where('week_year', $weekYear)
                    ->where('week_number', $weekNumber)
                    ->with(['case', 'claimant'])
                    ->orderBy('id')
                    ->each(function (Expense $expense) use ($stream): void {
                        fputcsv($stream, [
                            $expense->id,
                            $this->safeCsvText($expense->claimant->name),
                            $this->safeCsvText($expense->case->number),
                            $expense->week_number,
                            $expense->week_year,
                            $expense->task_date->toDateString(),
                            substr((string) $expense->start_time, 0, 5),
                            substr((string) $expense->end_time, 0, 5),
                            $expense->type->label(),
                            $this->safeCsvText($expense->description),
                            (string) $expense->amount,
                            $expense->status->label(),
                            $this->safeCsvText($expense->rejection_reason ?? ''),
                        ], ',', '"', '');
                    });
            } finally {
                fclose($stream);
            }
        }, sprintf('expense-report-%d-W%02d.csv', $weekYear, $weekNumber));
    }

    public function render(): View
    {
        $statuses = match ($this->activeTab) {
            'awaiting_approval' => [
                ExpenseStatus::AwaitingApproval,
                ExpenseStatus::RejectedAndReapply,
            ],
            'approved' => [ExpenseStatus::Approved],
            'rejected' => [ExpenseStatus::Rejected],
        };

        $expenses = Expense::query()
            ->whereIn('status', array_map(
                static fn (ExpenseStatus $status): string => $status->value,
                $statuses,
            ))
            ->with(['case', 'claimant', 'reviewer'])
            ->orderByDesc('week_year')
            ->orderByDesc('week_number')
            ->orderByDesc('task_date')
            ->orderByDesc('id')
            ->get();

        return view('expenses::livewire.expense-admin', [
            'expensesByWeek' => $expenses->groupBy(
                fn (Expense $expense): string => sprintf(
                    '%d-W%02d',
                    $expense->week_year,
                    $expense->week_number,
                ),
            ),
            'selectedExpense' => $this->selectedExpenseId
                ? Expense::query()
                    ->with(['case', 'claimant', 'reviewer'])
                    ->find($this->selectedExpenseId)
                : null,
        ]);
    }

    private function authenticatedUser(): User
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            abort(401);
        }

        return $user;
    }

    private function safeCsvText(string $value): string
    {
        return preg_match('/^\s*[=+\-@]/u', $value) === 1
            ? "'".$value
            : $value;
    }
}
