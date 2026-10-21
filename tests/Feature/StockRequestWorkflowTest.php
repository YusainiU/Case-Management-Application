<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseSource;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\StockManagement\Enums\StockRequestStatus;
use Simplydigital\StockManagement\Livewire\StockRequestCase;
use Simplydigital\StockManagement\Livewire\StockRequestQueue;
use Simplydigital\StockManagement\Models\StockRequest;
use Simplydigital\StockManagement\Services\StockRequestWorkflow;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class StockRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_case_editor_can_submit_request_and_add_it_to_case_progress_log(): void
    {
        $engineer = User::factory()->create();
        $this->grantPermissions($engineer, 'case.view', 'case.update');
        $case = $this->createCase($engineer);
        $this->actingAs($engineer);

        Livewire::test(StockRequestCase::class, ['case' => $case])
            ->set('partDescription', 'Replacement cooling fan')
            ->set('quantity', 2)
            ->set('notes', 'Required to complete the repair')
            ->call('createStockRequest')
            ->assertHasNoErrors()
            ->assertSee('Stock request submitted.');

        $stockRequest = StockRequest::query()->firstOrFail();
        $progressLog = TaskLogs::query()
            ->where('ticket_id', $case->id)
            ->firstOrFail();

        $this->assertSame(StockRequestStatus::Requested, $stockRequest->status);
        $this->assertSame('SR-'.str_pad((string) $stockRequest->id, 6, '0', STR_PAD_LEFT), $stockRequest->number);
        $this->assertSame('internal', $progressLog->source);
        $this->assertStringContainsString($stockRequest->number, $progressLog->description);
        $this->assertDatabaseHas('stock_request_updates', [
            'stock_request_id' => $stockRequest->id,
            'from_status' => null,
            'to_status' => StockRequestStatus::Requested->value,
        ]);
    }

    public function test_stock_manager_can_process_request_through_receipt_and_log_each_stage(): void
    {
        $engineer = User::factory()->create();
        $stockManager = User::factory()->create();
        $this->grantPermissions($engineer, 'case.view', 'case.update');
        $this->grantPermissions($stockManager, 'stock_request.manage');
        $case = $this->createCase($engineer);

        $stockRequest = app(StockRequestWorkflow::class)->create($case, $engineer, [
            'part_description' => 'Replacement drive',
            'quantity' => 1,
        ]);

        $this->actingAs($stockManager);
        $queue = Livewire::test(StockRequestQueue::class);

        foreach ([
            StockRequestStatus::Ordered,
            StockRequestStatus::Dispatched,
            StockRequestStatus::Received,
        ] as $status) {
            $queue
                ->call('selectRequest', $stockRequest->id)
                ->set('nextStatus', $status->value)
                ->set('stageNotes', 'Verified '.$status->label())
                ->call('saveProgress')
                ->assertHasNoErrors();
        }

        $stockRequest->refresh();

        $this->assertSame(StockRequestStatus::Received, $stockRequest->status);
        $this->assertDatabaseCount('stock_request_updates', 4);
        $this->assertSame(
            4,
            TaskLogs::query()->where('ticket_id', $case->id)->count(),
        );
        $this->assertDatabaseHas('stock_request_updates', [
            'stock_request_id' => $stockRequest->id,
            'from_status' => StockRequestStatus::Dispatched->value,
            'to_status' => StockRequestStatus::Received->value,
        ]);
    }

    public function test_workflow_rejects_skipping_a_request_stage(): void
    {
        $engineer = User::factory()->create();
        $stockManager = User::factory()->create();
        $case = $this->createCase($engineer);
        $stockRequest = app(StockRequestWorkflow::class)->create($case, $engineer, [
            'part_description' => 'Replacement display',
            'quantity' => 1,
        ]);

        try {
            app(StockRequestWorkflow::class)->transition(
                $stockRequest,
                StockRequestStatus::Dispatched,
                $stockManager,
                [],
            );
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('nextStatus', $exception->errors());
        }

        $this->assertSame(StockRequestStatus::Requested, $stockRequest->refresh()->status);
        $this->assertDatabaseCount('stock_request_updates', 1);
        $this->assertSame(1, TaskLogs::query()->where('ticket_id', $case->id)->count());
    }

    public function test_case_editor_cannot_open_stock_queue_without_manage_permission(): void
    {
        $engineer = User::factory()->create();
        $this->grantPermissions($engineer, 'case.update');
        $this->actingAs($engineer);

        $this->get(route('stock-requests.index'))
            ->assertForbidden();
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
            'short_description' => 'A test case requiring a part',
            'description' => 'Case details for a stock request test.',
            'state' => CaseState::Open,
            'contact_telephone' => '0123456789',
            'contact_email' => 'customer@example.test',
        ]);
    }
}
