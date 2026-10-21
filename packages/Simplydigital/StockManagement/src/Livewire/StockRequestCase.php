<?php

namespace Simplydigital\StockManagement\Livewire;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Simplydigital\CaseManagement\Models\Product;
use Simplydigital\CaseManagement\Models\Supplier;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\StockManagement\Models\StockRequest;
use Simplydigital\StockManagement\Services\StockRequestWorkflow;

class StockRequestCase extends Component
{
    public ?tickets $case = null;

    public string $partDescription = '';

    public string $quantity = '1';

    public ?string $productId = null;

    public ?string $supplierId = null;

    public string $notes = '';

    public ?string $message = null;

    public function mount(tickets $case): void
    {
        $this->case = $case;

        $this->authorize('view', $case);
        $this->authorize('viewAny', StockRequest::class);
    }

    public function createStockRequest(StockRequestWorkflow $workflow): void
    {
        $case = $this->case;

        if (! $case) {
            abort(404);
        }

        $this->authorize('create', [StockRequest::class, $case]);

        $validated = $this->validate([
            'partDescription' => ['required', 'string', 'max:2000'],
            'quantity' => ['required', 'integer', 'min:1'],
            'productId' => [
                'nullable',
                'integer',
                Rule::exists('products', 'id')->where('active', true),
            ],
            'supplierId' => [
                'nullable',
                'integer',
                Rule::exists('suppliers', 'id')->where('active', true),
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $workflow->create($case, $this->authenticatedUser(), [
            'part_description' => trim($validated['partDescription']),
            'quantity' => (int) $validated['quantity'],
            'product_id' => filled($validated['productId'] ?? null)
                ? (int) $validated['productId']
                : null,
            'supplier_id' => filled($validated['supplierId'] ?? null)
                ? (int) $validated['supplierId']
                : null,
            'notes' => filled($validated['notes'] ?? null) ? trim($validated['notes']) : null,
        ]);

        $this->reset([
            'partDescription',
            'productId',
            'supplierId',
            'notes',
        ]);
        $this->quantity = '1';
        $this->message = 'Stock request submitted.';
    }

    public function render(): View
    {
        $case = $this->case;

        if (! $case) {
            abort(404);
        }

        return view('stockmanagement::livewire.stock-request-case', [
            'stockRequests' => StockRequest::query()
                ->where('case_id', $case->id)
                ->with(['product', 'supplier', 'requester', 'updates.user'])
                ->latest()
                ->get(),
            'products' => Product::query()
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'product_code', 'model']),
            'suppliers' => Supplier::query()
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'number']),
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
