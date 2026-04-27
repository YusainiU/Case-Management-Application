<?php

namespace Simplydigital\CaseManagement\Livewire\Supplier;

use LivewireUI\Modal\ModalComponent;
class SupplierProfile extends ModalComponent
{
    public $supplierId;
    public $supplier;
    public $supplierItems = [];
    public $title = 'Supplier Profile';


    public function mount($supplierId)
    {
        $this->supplierId = $supplierId;
        $caseSupplierServices = app('case-supplier-services');
        $this->supplier = $caseSupplierServices->getSupplierById($supplierId);
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
