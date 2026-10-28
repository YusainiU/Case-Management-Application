<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseSource;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\Expenses\Enums\ExpenseStatus;
use Simplydigital\Expenses\Livewire\ExpenseAdmin;
use Simplydigital\Expenses\Livewire\ExpenseClaims;
use Simplydigital\Expenses\Models\Expense;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ExpenseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_claimant_can_submit_case_expense_with_receipt_and_only_sees_their_claims(): void
    {
        Storage::fake('local');

        $claimant = User::factory()->create();
        $anotherUser = User::factory()->create();
        $this->grantPermissions($claimant, 'case.view', 'expense.create', 'expense.view');
        $this->grantPermissions($anotherUser, 'expense.view');
        $case = $this->createCase($claimant);

        $this->actingAs($claimant);

        Livewire::test(ExpenseClaims::class, ['case' => $case])
            ->set('weekNumber', '44')
            ->set('weekYear', '2026')
            ->set('taskDate', '2026-10-26')
            ->set('startTime', '09:15')
            ->set('endTime', '10:45')
            ->set('type', 'site_visit')
            ->set('description', 'Travel to the customer site')
            ->set('amount', '34.50')
            ->set('receipt', UploadedFile::fake()->image('receipt.png'))
            ->call('saveExpense')
            ->assertHasNoErrors()
            ->assertSee('Expense claim submitted for approval.')
            ->assertSee('£34.50');

        $expense = Expense::query()->firstOrFail();

        $this->assertSame($claimant->id, $expense->user_id);
        $this->assertSame($case->id, $expense->case_id);
        $this->assertSame(ExpenseStatus::AwaitingApproval, $expense->status);
        $this->assertSame(44, $expense->week_number);
        $this->assertSame(2026, $expense->week_year);
        Storage::disk('local')->assertExists($expense->receipt_path);

        $this->actingAs($anotherUser);

        Livewire::test(ExpenseClaims::class)
            ->assertDontSee('Travel to the customer site')
            ->assertDontSee('£34.50');
    }

    public function test_task_date_must_belong_to_the_selected_iso_week(): void
    {
        $claimant = User::factory()->create();
        $this->grantPermissions($claimant, 'case.view', 'expense.create');
        $case = $this->createCase($claimant);

        $this->actingAs($claimant);

        Livewire::test(ExpenseClaims::class, ['case' => $case])
            ->set('weekNumber', '45')
            ->set('weekYear', '2026')
            ->set('taskDate', '2026-10-26')
            ->set('startTime', '09:00')
            ->set('endTime', '10:00')
            ->set('type', 'other')
            ->set('description', 'A valid-looking task')
            ->set('amount', '10.00')
            ->call('saveExpense')
            ->assertHasErrors(['taskDate']);

        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_claimant_can_update_pending_expense_without_changing_its_status(): void
    {
        $claimant = User::factory()->create();
        $this->grantPermissions($claimant, 'case.view', 'expense.view', 'expense.update');
        $case = $this->createCase($claimant);
        $expense = Expense::factory()->create([
            'case_id' => $case->id,
            'user_id' => $claimant->id,
            'week_number' => 44,
            'week_year' => 2026,
            'task_date' => '2026-10-26',
            'description' => 'Original description',
        ]);

        $this->actingAs($claimant);

        Livewire::test(ExpenseClaims::class)
            ->call('editExpense', $expense->id)
            ->set('description', 'Updated description')
            ->call('saveExpense')
            ->assertHasNoErrors();

        $this->assertSame('Updated description', $expense->refresh()->description);
        $this->assertSame(ExpenseStatus::AwaitingApproval, $expense->status);
    }

    public function test_claimant_can_delete_pending_expense_and_its_receipt(): void
    {
        Storage::fake('local');

        $claimant = User::factory()->create();
        $this->grantPermissions($claimant, 'case.view', 'expense.view', 'expense.delete');
        $case = $this->createCase($claimant);
        $receiptPath = 'expenses/receipts/receipt.png';
        Storage::disk('local')->put($receiptPath, 'receipt content');
        $expense = Expense::factory()->create([
            'case_id' => $case->id,
            'user_id' => $claimant->id,
            'receipt_path' => $receiptPath,
        ]);

        $this->actingAs($claimant);

        Livewire::test(ExpenseClaims::class, ['case' => $case])
            ->call('deleteExpense', $expense->id)
            ->assertHasNoErrors()
            ->assertSee('Expense claim deleted.');

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
        Storage::disk('local')->assertMissing($receiptPath);
    }

    public function test_manager_rejection_can_be_reapplied_and_approved_claims_are_locked(): void
    {
        $claimant = User::factory()->create();
        $manager = User::factory()->create();
        $this->grantPermissions(
            $claimant,
            'case.view',
            'expense.create',
            'expense.view',
            'expense.update',
            'expense.delete',
        );
        $this->grantPermissions($manager, 'expense.admin');
        $case = $this->createCase($claimant);

        $expense = Expense::factory()->create([
            'case_id' => $case->id,
            'user_id' => $claimant->id,
            'week_number' => 44,
            'week_year' => 2026,
            'task_date' => '2026-10-26',
            'description' => 'Taxi fare',
            'amount' => '22.00',
        ]);

        $this->actingAs($manager);

        Livewire::test(ExpenseAdmin::class)
            ->call('rejectExpense', $expense->id)
            ->assertHasErrors(['rejectionReason']);

        $this->assertSame(ExpenseStatus::AwaitingApproval, $expense->refresh()->status);

        Livewire::test(ExpenseAdmin::class)
            ->set('rejectionReason', 'Please attach the receipt.')
            ->call('rejectExpense', $expense->id)
            ->assertHasNoErrors();

        $this->assertSame(ExpenseStatus::Rejected, $expense->refresh()->status);
        $this->assertSame('Please attach the receipt.', $expense->rejection_reason);

        $this->actingAs($claimant);

        Livewire::test(ExpenseClaims::class)
            ->assertSee('Please attach the receipt.')
            ->call('editExpense', $expense->id)
            ->set('description', 'Taxi fare with receipt')
            ->call('saveExpense')
            ->assertHasNoErrors()
            ->assertSee('Expense claim reapplied for approval.');

        $expense->refresh();
        $this->assertSame(ExpenseStatus::RejectedAndReapply, $expense->status);
        $this->assertSame('Please attach the receipt.', $expense->rejection_reason);

        $this->actingAs($manager);

        Livewire::test(ExpenseAdmin::class)
            ->assertSee('Rejected and reapply')
            ->call('approveExpense', $expense->id)
            ->assertHasNoErrors();

        $expense->refresh();
        $this->assertSame(ExpenseStatus::Approved, $expense->status);
        $this->assertSame($manager->id, $expense->reviewed_by);
        $this->assertNotNull($expense->reviewed_at);
        $this->assertFalse(Gate::forUser($claimant)->allows('update', $expense));
        $this->assertFalse(Gate::forUser($claimant)->allows('delete', $expense));
    }

    public function test_non_admin_cannot_open_expense_approval_list(): void
    {
        $user = User::factory()->create();
        $this->grantPermissions($user, 'expense.view');

        $this->actingAs($user)
            ->get(route('expenses.admin'))
            ->assertForbidden();
    }

    public function test_expense_admin_route_renders_the_approval_page(): void
    {
        $manager = User::factory()->create();
        $claimant = User::factory()->create();
        $this->grantPermissions($manager, 'expense.admin');
        $this->grantPermissions($claimant, 'expense.view');

        $this->actingAs($manager)
            ->get(route('expenses.admin'))
            ->assertOk()
            ->assertSee('Expense approvals')
            ->assertSee('Download report');

        $this->actingAs($claimant)
            ->get(route('expenses.index'))
            ->assertOk()
            ->assertSee('My expense claims');
    }

    public function test_admin_cannot_review_their_own_claim(): void
    {
        $claimant = User::factory()->create();
        $this->grantPermissions($claimant, 'case.view', 'expense.admin');
        $case = $this->createCase($claimant);
        $expense = Expense::factory()->create([
            'case_id' => $case->id,
            'user_id' => $claimant->id,
        ]);

        $this->assertFalse(Gate::forUser($claimant)->allows('review', $expense));
    }

    public function test_admin_report_contains_only_claims_for_the_selected_iso_week(): void
    {
        $manager = User::factory()->create();
        $claimant = User::factory()->create(['name' => 'Report claimant']);
        $this->grantPermissions($manager, 'expense.admin');
        $case = $this->createCase($claimant);

        $expense = Expense::factory()->create([
            'case_id' => $case->id,
            'user_id' => $claimant->id,
            'week_number' => 44,
            'week_year' => 2026,
            'task_date' => '2026-10-26',
            'description' => 'Taxi fare',
            'amount' => '12.50',
        ]);

        Expense::factory()->create([
            'case_id' => $case->id,
            'user_id' => $claimant->id,
            'week_number' => 45,
            'week_year' => 2026,
            'task_date' => '2026-11-02',
            'description' => 'Out of week',
        ]);

        $expectedCsv = implode("\n", [
            '"Expense ID",Claimant,Case,Week,Year,"Task date","Start time","End time",Type,Description,"Amount (GBP)",Status,"Rejection reason"',
            $expense->id.',"Report claimant",'.$case->number.',44,2026,2026-10-26,09:00,10:00,Others,"Taxi fare",12.50,"Awaiting approval",',
            '',
        ]);

        $this->actingAs($manager);

        Livewire::test(ExpenseAdmin::class)
            ->set('reportWeek', '44')
            ->set('reportYear', '2026')
            ->call('downloadReport')
            ->assertFileDownloaded('expense-report-2026-W44.csv', $expectedCsv);
    }

    private function grantPermissions(User $user, string ...$permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $user->givePermissionTo($permissions);
    }

    private function createCase(User $creator): tickets
    {
        $customer = Customer::factory()->create();

        return tickets::query()->create([
            'customer_id' => $customer->id,
            'category' => CaseCategory::Failure,
            'created_by' => $creator->name,
            'source' => CaseSource::Phone,
            'short_description' => 'An expense test case',
            'description' => 'Case details for an expense workflow test.',
            'state' => CaseState::Open,
            'contact_telephone' => '0123456789',
            'contact_email' => 'customer@example.test',
        ]);
    }
}
