<?php

namespace Simplydigital\CaseManagement\Livewire\Supplier;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Models\Supplier;
class SupplierProfile extends ModalComponent
{
    public int|string|null $supplierId = null;
    public ?supplier $supplier = null;
    public array $supplierItems = [];
    public string $title = 'Supplier Profile';


    public function mount()
    {
        $this->authorize('viewAny', Supplier::class);
        $caseSupplierServices = app('case-supplier-services');
        $this->supplier = $caseSupplierServices->getSupplierById($this->supplierId);
        $this->buildProfileItems();
    }

    public function buildProfileItems()
    {
        $caseSupplierServices = app('case-supplier-services');
        $items = $caseSupplierServices->buildProfileItems($this->supplier);
        $this->supplierItems = $items;
    }

    public function closeThisModal()
    {
        $this->dispatch('closeProfileModal');   
    }

    public function render()
    {
        return view('casemanagement::livewire.supplier.supplier-profile');
    }
}
