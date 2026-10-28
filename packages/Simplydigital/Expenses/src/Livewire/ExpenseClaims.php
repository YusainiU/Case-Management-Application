<?php

namespace Simplydigital\Expenses\Livewire;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\Expenses\Enums\ExpenseStatus;
use Simplydigital\Expenses\Enums\ExpenseType;
use Simplydigital\Expenses\Models\Expense;
use Simplydigital\Expenses\Services\ExpenseWorkflow;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('components.layouts.app')]
class ExpenseClaims extends Component
{
    use WithFileUploads;

    #[Locked]
    public ?tickets $case = null;

    #[Locked]
    public ?int $caseId = null;

    public ?int $editingExpenseId = null;

    public bool $showForm = false;

    public string $weekNumber = '';

    public string $weekYear = '';

    public string $taskDate = '';

    public string $startTime = '';

    public string $endTime = '';

    public string $type = '';

    public string $description = '';

    public string $amount = '';

    public ?TemporaryUploadedFile $receipt = null;

    public ?string $message = null;

    public function mount(?tickets $case = null): void
    {
        $this->case = $case;
        $this->caseId = $case?->id;

        if ($case) {
            $this->authorize('view', $case);

            abort_unless(
                auth()->user()?->can('expense.create')
                    || auth()->user()?->can('expense.view'),
                403,
            );
        } else {
            $this->authorize('viewAny', Expense::class);
        }

        $this->setCurrentWeekDefaults();
        $this->showForm = $case !== null
            && (auth()->user()?->can('expense.create') ?? false);
    }

    public function startNewExpense(): void
    {
        if (! $this->caseId) {
            abort(404);
        }

        $this->authorize('create', Expense::class);
        $this->editingExpenseId = null;
        $this->showForm = true;
        $this->resetForm();
    }

    public function editExpense(int $expenseId): void
    {
        $expense = $this->ownedExpense($expenseId);
        $this->authorize('update', $expense);

        $this->editingExpenseId = $expense->id;
        $this->showForm = true;
        $this->case = $expense->case;
        $this->weekNumber = (string) $expense->week_number;
        $this->weekYear = (string) $expense->week_year;
        $this->taskDate = $expense->task_date->toDateString();
        $this->startTime = substr((string) $expense->start_time, 0, 5);
        $this->endTime = substr((string) $expense->end_time, 0, 5);
        $this->type = $expense->type->value;
        $this->description = $expense->description;
        $this->amount = (string) $expense->amount;
        $this->receipt = null;
    }

    public function saveExpense(ExpenseWorkflow $workflow): void
    {
        if (! $this->case) {
            abort(404);
        }

        $expense = $this->editingExpenseId
            ? $this->ownedExpense($this->editingExpenseId)
            : null;

        if ($expense) {
            $this->authorize('update', $expense);
        } else {
            $this->authorize('create', Expense::class);
        }

        $validated = $this->validate([
            'weekNumber' => ['required', 'integer', 'between:1,53'],
            'weekYear' => ['required', 'integer', 'between:1900,9999'],
            'taskDate' => ['required', 'date_format:Y-m-d'],
            'startTime' => ['required', 'date_format:H:i'],
            'endTime' => ['required', 'date_format:H:i', 'after:startTime'],
            'type' => ['required', Rule::enum(ExpenseType::class)],
            'description' => ['required', 'string', 'max:5000'],
            'amount' => ['required', 'decimal:0,2', 'min:0.01', 'max:99999999.99'],
            'receipt' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $taskDate = CarbonImmutable::createFromFormat('!Y-m-d', $validated['taskDate']);

        if (
            $taskDate->isoWeek() !== (int) $validated['weekNumber']
            || $taskDate->isoWeekYear() !== (int) $validated['weekYear']
        ) {
            throw ValidationException::withMessages([
                'taskDate' => 'The task date must fall within the selected ISO week and year.',
            ]);
        }

        $isReapplication = $expense?->status === ExpenseStatus::Rejected;

        $workflow->save(
            case: $this->case,
            user: $this->authenticatedUser(),
            attributes: [
                'week_number' => (int) $validated['weekNumber'],
                'week_year' => (int) $validated['weekYear'],
                'task_date' => $validated['taskDate'],
                'start_time' => $validated['startTime'],
                'end_time' => $validated['endTime'],
                'type' => $validated['type'],
                'description' => trim($validated['description']),
                'amount' => (string) $validated['amount'],
            ],
            expense: $expense,
            receipt: $this->receipt,
            reapply: $isReapplication,
        );

        $this->message = $isReapplication
            ? 'Expense claim reapplied for approval.'
            : ($expense ? 'Expense claim updated.' : 'Expense claim submitted for approval.');

        $this->editingExpenseId = null;
        $this->showForm = false;
        $this->resetForm();
    }

    public function deleteExpense(int $expenseId, ExpenseWorkflow $workflow): void
    {
        $expense = $this->ownedExpense($expenseId);
        $this->authorize('delete', $expense);
        $workflow->delete($expense, $this->authenticatedUser());

        if ($this->editingExpenseId === $expense->id) {
            $this->editingExpenseId = null;
            $this->showForm = false;
            $this->resetForm();
        }

        $this->message = 'Expense claim deleted.';
    }

    public function cancelEditing(): void
    {
        $this->editingExpenseId = null;
        $this->showForm = false;
        $this->resetForm();
    }

    public function downloadReceipt(int $expenseId): StreamedResponse
    {
        $expense = $this->ownedExpense($expenseId);
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

    public function render(): View
    {
        $canViewClaims = auth()->user()?->can('expense.view') ?? false;
        $expenses = $canViewClaims
            ? Expense::query()
                ->where('user_id', $this->authenticatedUser()->id)
                ->when($this->caseId, fn ($query) => $query->where('case_id', $this->caseId))
                ->with('case')
                ->orderByDesc('week_year')
                ->orderByDesc('week_number')
                ->orderByDesc('task_date')
                ->orderByDesc('id')
                ->get()
            : collect();

        return view('expenses::livewire.expense-claims', [
            'expensesByWeek' => $expenses->groupBy(
                fn (Expense $expense): string => $this->weekKey($expense),
            ),
            'totalClaimed' => $expenses->sum(fn (Expense $expense): float => (float) $expense->amount),
            'expenseTypes' => ExpenseType::cases(),
            'expenseTypeOptions' => collect(ExpenseType::cases())
                ->mapWithKeys(
                    fn (ExpenseType $expenseType): array => [
                        $expenseType->value => $expenseType->label(),
                    ],
                )
                ->all(),
            'canCreate' => auth()->user()?->can('expense.create') ?? false,
            'canViewClaims' => $canViewClaims,
        ]);
    }

    private function ownedExpense(int $expenseId): Expense
    {
        return Expense::query()
            ->where('user_id', $this->authenticatedUser()->id)
            ->when($this->caseId, fn ($query) => $query->where('case_id', $this->caseId))
            ->with('case')
            ->findOrFail($expenseId);
    }

    private function authenticatedUser(): User
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            abort(401);
        }

        return $user;
    }

    private function setCurrentWeekDefaults(): void
    {
        $today = now();
        $this->weekNumber = (string) $today->isoWeek();
        $this->weekYear = (string) $today->isoWeekYear();
        $this->taskDate = $today->toDateString();
        $this->startTime = '09:00';
        $this->endTime = '10:00';
        $this->type = ExpenseType::Other->value;
    }

    private function resetForm(): void
    {
        $this->reset([
            'description',
            'amount',
            'receipt',
        ]);
        $this->setCurrentWeekDefaults();

        if ($this->caseId) {
            $this->case = tickets::query()->findOrFail($this->caseId);
        } else {
            $this->case = null;
        }
    }

    private function weekKey(Expense $expense): string
    {
        return sprintf(
            '%d-W%02d',
            $expense->week_year,
            $expense->week_number,
        );
    }
}
