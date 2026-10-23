<?php

namespace Simplydigital\StockManagement\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\StockManagement\Enums\StockRequestStatus;
use Simplydigital\StockManagement\Models\StockRequest;
use Simplydigital\CaseManagement\Enums\CaseState;

class StockRequestWorkflow
{
    /**
     * @param array{
     *     product_id?: int|null,
     *     supplier_id?: int|null,
     *     part_description: string,
     *     quantity: int,
     *     notes?: string|null
     * } $attributes
     */
    public function create(tickets $case, User $user, array $attributes): StockRequest
    {
        return DB::transaction(function () use ($case, $user, $attributes): StockRequest {
            $stockRequest = StockRequest::query()->create([
                'number' => 'SR-TEMP-'.Str::uuid()->toString(),
                'case_id' => $case->id,
                'product_id' => $attributes['product_id'] ?? null,
                'supplier_id' => $attributes['supplier_id'] ?? null,
                'requested_by' => $user->id,
                'part_description' => $attributes['part_description'],
                'quantity' => $attributes['quantity'],
                'notes' => $attributes['notes'] ?? null,
                'status' => StockRequestStatus::Requested,
            ]);

            $stockRequest->number = 'SR-'.str_pad((string) $stockRequest->id, 6, '0', STR_PAD_LEFT);
            $stockRequest->save();

            $case->state = CaseState::Part_Request->value;
            $case->save();

            $this->recordStatus(
                $stockRequest,
                $user,
                null,
                StockRequestStatus::Requested,
                $stockRequest->notes,
            );

            return $stockRequest->load(['case', 'product', 'supplier', 'requester', 'updates.user']);
        });
    }

    /**
     * @param array{
     *     supplier_id?: int|null,
     *     supplier_reference?: string|null,
     *     tracking_reference?: string|null,
     *     notes?: string|null
     * } $attributes
     */
    public function transition(
        StockRequest $stockRequest,
        StockRequestStatus $status,
        User $user,
        array $attributes,
    ): StockRequest {
        return DB::transaction(function () use ($stockRequest, $status, $user, $attributes): StockRequest {
            $stockRequest = StockRequest::query()
                ->lockForUpdate()
                ->findOrFail($stockRequest->id);

            $currentStatus = $stockRequest->status;

            if (! $currentStatus->canTransitionTo($status)) {
                throw ValidationException::withMessages([
                    'nextStatus' => "A request cannot move from {$currentStatus->label()} to {$status->label()}.",
                ]);
            }

            $stockRequest->fill([
                'supplier_id' => array_key_exists('supplier_id', $attributes)
                    ? $attributes['supplier_id']
                    : $stockRequest->supplier_id,
                'supplier_reference' => array_key_exists('supplier_reference', $attributes)
                    ? $attributes['supplier_reference']
                    : $stockRequest->supplier_reference,
                'tracking_reference' => array_key_exists('tracking_reference', $attributes)
                    ? $attributes['tracking_reference']
                    : $stockRequest->tracking_reference,
                'status' => $status,
            ])->save();

            $this->recordStatus(
                $stockRequest,
                $user,
                $currentStatus,
                $status,
                $attributes['notes'] ?? null,
            );

            return $stockRequest->load(['case', 'product', 'supplier', 'requester', 'updates.user']);
        });
    }

    private function recordStatus(
        StockRequest $stockRequest,
        User $user,
        ?StockRequestStatus $fromStatus,
        StockRequestStatus $toStatus,
        ?string $notes,
    ): void {
        $stockRequest->loadMissing(['case', 'supplier']);

        $description = sprintf(
            'Stock request %s for "%s" (quantity %d) is now %s. Updated by %s.',
            $stockRequest->number,
            $stockRequest->part_description,
            $stockRequest->quantity,
            $toStatus->label(),
            $user->name,
        );

        $details = array_filter([
            $stockRequest->supplier?->name ? 'Supplier: '.$stockRequest->supplier->name : null,
            $stockRequest->supplier_reference ? 'Supplier reference: '.$stockRequest->supplier_reference : null,
            $stockRequest->tracking_reference ? 'Tracking reference: '.$stockRequest->tracking_reference : null,
            filled($notes) ? 'Note: '.trim($notes) : null,
        ]);

        if ($details !== []) {
            $description .= ' '.implode('; ', $details);
        }

        $taskLog = TaskLogs::query()->create([
            'name' => $stockRequest->case->number,
            'short_description' => Str::limit('Stock request '.$stockRequest->number.': '.$toStatus->label(), 255),
            'type' => config('case-management.logs.type.internal_update', 'internal_update_log'),
            'source' => config('case-management.logs.source.internal', 'internal'),
            'user_id' => $user->id,
            'ticket_id' => $stockRequest->case_id,
            'description' => $description,
        ]);

        $stockRequest->updates()->create([
            'user_id' => $user->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'notes' => filled($notes) ? trim($notes) : null,
            'task_log_id' => $taskLog->id,
        ]);

        if($toStatus->label() === StockRequestStatus::Received->label())
        {
            $case = $stockRequest->case;
            $case->state = CaseState::In_Progress->value;
            $case->save();
        }

    }
}
