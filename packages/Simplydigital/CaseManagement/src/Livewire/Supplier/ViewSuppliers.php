<?php

namespace Simplydigital\CaseManagement\Livewire\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class ViewSuppliers extends Component
{
    use WithPagination;
    public $title = 'Suppliers';
    public $supplierId;
    public $supplier;
    public $filter;
    public $sortField;
    public $sortDirection;
    public $displayCreateModal = false;
    public $displayUpdateModal = false;
    public $displayProfileModal = false;
    protected $listeners = [
        'closeCreateSupplierModal' => 'closeCreateModal',
        'closeUpdateSupplierModal' => 'closeUpdateModal',
        'closeProfileModal' => 'closeProfileModal',
    ];

    public function closeCreateModal()
    {
        $this->displayCreateModal = false;  
    }

    public function closeUpdateModal()
    {        
        $this->displayUpdateModal = false;
    }

    public function closeProfileModal()
    {        
        $this->displayProfileModal = false;
    }

    public function loadSuppliers()
    {
        $caseSupplierServices = app('case-supplier-services');
        if($this->sortField){
            $caseSupplierServices->sortByField($this->sortField, $this->sortDirection);
        }
        if($this->filter){
            $caseSupplierServices->filterResult($this->filter);
        }        
        $query = $caseSupplierServices->getAllActiveSuppliers();
        return $query;
    }

    public function sortByField($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function resetAll()
    {
        $this->reset([
            'filter', 
            'sortField', 
            'sortDirection',
            'displayCreateModal',
            'displayUpdateModal',
            'displayProfileModal',
        ]);
    }

    public function openCreateModal()
    {
        $this->resetAll();
        $this->displayCreateModal = true;
    }

    public function openUpdateModal($supplierId)
    {
        $this->resetAll();
        $this->supplierId = $supplierId;
        $this->displayUpdateModal = true;
    }

    public function openProfileModal($supplierId)
    {
        $this->resetAll();
        $this->supplierId = $supplierId;
        $this->displayProfileModal = true;
    }

    public function render()
    {
        $suppliers = $this->loadSuppliers()->paginate(10);
        return view('casemanagement::livewire.supplier.view-suppliers', [
            'suppliers' => $suppliers,
        ]);
    }

}   