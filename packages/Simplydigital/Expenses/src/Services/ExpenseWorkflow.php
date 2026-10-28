<?php

namespace Simplydigital\Expenses\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\Expenses\Enums\ExpenseStatus;
use Simplydigital\Expenses\Models\Expense;
use Throwable;

class ExpenseWorkflow
{
    /**
     * @param array{
     *     week_number: int,
     *     week_year: int,
     *     task_date: string,
     *     start_time: string,
     *     end_time: string,
     *     type: string,
     *     description: string,
     *     amount: string
     * } $attributes
     */
    public function save(
        tickets $case,
        User $user,
        array $attributes,
        ?Expense $expense = null,
        ?UploadedFile $receipt = null,
        bool $reapply = false,
    ): Expense {
        $newReceiptPath = $receipt?->store('expenses/receipts', 'local');

        if ($receipt && ! is_string($newReceiptPath)) {
            throw new \RuntimeException('The expense receipt could not be stored.');
        }

        try {
            $savedExpense = DB::transaction(function () use (
                $case,
                $user,
                $attributes,
                $expense,
                $newReceiptPath,
                $reapply,
            ): Expense {
                $savedExpense = $expense
                    ? Expense::query()->lockForUpdate()->findOrFail($expense->id)
                    : new Expense;

                if ($expense && (
                    $savedExpense->user_id !== $user->id
                    || $savedExpense->case_id !== $case->id
                )) {
                    abort(404);
                }

                if ($expense && ! in_array($savedExpense->status, [
                    ExpenseStatus::AwaitingApproval,
                    ExpenseStatus::Rejected,
                ], true)) {
                    throw ValidationException::withMessages([
                        'expense' => 'This expense can no longer be changed.',
                    ]);
                }

                if ($reapply && $savedExpense->status !== ExpenseStatus::Rejected) {
                    throw ValidationException::withMessages([
                        'expense' => 'Only rejected expenses can be reapplied.',
                    ]);
                }

                $savedExpense->fill([
                    'case_id' => $case->id,
                    'user_id' => $user->id,
                    ...$attributes,
                    'status' => $reapply
                        ? ExpenseStatus::RejectedAndReapply
                        : ($expense?->status ?? ExpenseStatus::AwaitingApproval),
                ]);

                if ($newReceiptPath) {
                    $savedExpense->receipt_path = $newReceiptPath;
                }

                $savedExpense->save();

                return $savedExpense;
            });
        } catch (Throwable $exception) {
            if ($newReceiptPath) {
                Storage::disk('local')->delete($newReceiptPath);
            }

            throw $exception;
        }

        if ($newReceiptPath && $expense?->receipt_path) {
            Storage::disk('local')->delete($expense->receipt_path);
        }

        return $savedExpense;
    }

    public function delete(Expense $expense, User $user): void
    {
        $receiptPath = DB::transaction(function () use ($expense, $user): ?string {
            $lockedExpense = Expense::query()
                ->lockForUpdate()
                ->findOrFail($expense->id);

            if (
                $lockedExpense->user_id !== $user->id
                || $lockedExpense->status !== ExpenseStatus::AwaitingApproval
            ) {
                throw ValidationException::withMessages([
                    'expense' => 'This expense can no longer be deleted.',
                ]);
            }

            $receiptPath = $lockedExpense->receipt_path;
            $lockedExpense->delete();

            return $receiptPath;
        });

        if ($receiptPath) {
            Storage::disk('local')->delete($receiptPath);
        }
    }

    public function review(
        Expense $expense,
        User $reviewer,
        ExpenseStatus $status,
        ?string $rejectionReason = null,
    ): Expense {
        return DB::transaction(function () use (
            $expense,
            $reviewer,
            $status,
            $rejectionReason,
        ): Expense {
            $lockedExpense = Expense::query()
                ->lockForUpdate()
                ->findOrFail($expense->id);

            if (! $lockedExpense->status->canBeReviewed()) {
                throw ValidationException::withMessages([
                    'expense' => 'This expense has already been reviewed.',
                ]);
            }

            if ($lockedExpense->user_id === $reviewer->id) {
                throw ValidationException::withMessages([
                    'expense' => 'You cannot review your own expense claim.',
                ]);
            }

            if ($status === ExpenseStatus::Rejected && blank($rejectionReason)) {
                throw ValidationException::withMessages([
                    'rejectionReason' => 'A reason is required when rejecting an expense.',
                ]);
            }

            if (! in_array($status, [ExpenseStatus::Approved, ExpenseStatus::Rejected], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Choose an approval or rejection decision.',
                ]);
            }

            $lockedExpense->fill([
                'status' => $status,
                'rejection_reason' => $status === ExpenseStatus::Rejected
                    ? trim((string) $rejectionReason)
                    : null,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ])->save();

            return $lockedExpense;
        });
    }
}
