<?php

namespace Simplydigital\StockManagement\Livewire;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Simplydigital\CaseManagement\Models\Supplier;
use Simplydigital\StockManagement\Enums\StockRequestStatus;
use Simplydigital\StockManagement\Models\StockRequest;
use Simplydigital\StockManagement\Services\StockRequestWorkflow;

#[Layout('components.layouts.app')]
class StockRequestQueue extends Component
{
    public ?int $selectedRequestId = null;

    public ?string $nextStatus = null;

    public ?string $supplierId = null;

    public ?string $supplierReference = null;

    public ?string $trackingReference = null;

    public ?string $stageNotes = null;

    public ?string $message = null;

    public function mount(): void
    {
        $this->authorize('manageAny', StockRequest::class);
    }

    public function selectRequest(int $requestId): void
    {
        $stockRequest = StockRequest::query()->findOrFail($requestId);
        $this->authorize('update', $stockRequest);

        $nextStatus = $stockRequest->status->nextStatuses()[0] ?? null;

        if (! $nextStatus) {
            return;
        }

        $this->selectedRequestId = $stockRequest->id;
        $this->nextStatus = $nextStatus->value;
        $this->supplierId = $stockRequest->supplier_id === null
            ? null
            : (string) $stockRequest->supplier_id;
        $this->supplierReference = $stockRequest->supplier_reference;
        $this->trackingReference = $stockRequest->tracking_reference;
        $this->stageNotes = null;
    }

    public function saveProgress(StockRequestWorkflow $workflow): void
    {
        if (! $this->selectedRequestId) {
            abort(404);
        }

        $stockRequest = StockRequest::query()->findOrFail($this->selectedRequestId);
        $this->authorize('update', $stockRequest);

        $allowedStatuses = array_map(
            static fn (StockRequestStatus $status): string => $status->value,
            $stockRequest->status->nextStatuses(),
        );

        $validated = $this->validate([
            'nextStatus' => ['required', Rule::in($allowedStatuses)],
            'supplierId' => [
                'nullable',
                'integer',
                Rule::exists('suppliers', 'id')->where('active', true),
            ],
            'supplierReference' => ['nullable', 'string', 'max:255'],
            'trackingReference' => ['nullable', 'string', 'max:255'],
            'stageNotes' => ['nullable', 'string', 'max:2000'],
        ]);

        $workflow->transition(
            $stockRequest,
            StockRequestStatus::from($validated['nextStatus']),
            $this->authenticatedUser(),
            [
                'supplier_id' => filled($validated['supplierId'] ?? null)
                    ? (int) $validated['supplierId']
                    : null,
                'supplier_reference' => $validated['supplierReference'] ?? null,
                'tracking_reference' => $validated['trackingReference'] ?? null,
                'notes' => filled($validated['stageNotes'] ?? null)
                    ? trim($validated['stageNotes'])
                    : null,
            ],
        );

        $this->reset([
            'selectedRequestId',
            'nextStatus',
            'supplierId',
            'supplierReference',
            'trackingReference',
            'stageNotes',
        ]);
        $this->message = 'Stock request progress updated.';
        $this->dispatch('reloadCaseStatus');
    }

    public function cancelEditing(): void
    {
        $this->reset([
            'selectedRequestId',
            'nextStatus',
            'supplierId',
            'supplierReference',
            'trackingReference',
            'stageNotes',
        ]);
    }

    public function render(): View
    {
        $stockRequests = StockRequest::query()
            ->whereNotIn('status', [
                StockRequestStatus::Received->value,
                StockRequestStatus::Cancelled->value,
            ])
            ->with(['case', 'product', 'supplier', 'requester'])
            ->latest()
            ->get();

        $selectedRequest = $this->selectedRequestId
            ? StockRequest::query()
                ->with(['case', 'product', 'supplier', 'requester'])
                ->find($this->selectedRequestId)
            : null;

        return view('stockmanagement::livewire.stock-request-queue', [
            'stockRequests' => $stockRequests,
            'selectedRequest' => $selectedRequest,
            'suppliers' => Supplier::query()
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'number']),
            'nextStatuses' => $selectedRequest?->status->nextStatuses() ?? [],
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
}
