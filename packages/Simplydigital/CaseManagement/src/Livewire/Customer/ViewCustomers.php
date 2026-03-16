<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use Livewire\Component;
use Livewire\WithPagination;

class ViewCustomers extends Component
{
    use WithPagination; 
    public $customerId;
    public $displayCreateModal = false;
    public $displayUpdateModal = false;
    public $sortField;
    public $sortDirection = null;
    public $filter;
    protected $listeners = ['closeCreateModal'];

    public function mount()
    {
        $this->getAllActiveCustomers();
    }

    public function sortByField($fieldName, $direction)
    {
        if($this->sortField == $fieldName)
        {
            $this->sortField = null;
        }else{
            $this->sortField = $fieldName;
            $this->sortDirection = $direction;
        }
        $this->getAllActiveCustomers();
    }

    public function getAllActiveCustomers()
    {
        $utilityService = app('case-customer-services');
        if($this->sortField){
            $utilityService->sortByField($this->sortField, $this->sortDirection);
        }
        if($this->filter){
            $utilityService->filterResult($this->filter);
        }
        // if($this->filter){
        //     dd($utilityService->getAllActiveCustomers()->dumpRawSql());
        // }
        return $utilityService->getAllActiveCustomers();        
    }

    public function resetAll()
    {
        $this->displayCreateModal = false;
        $this->displayUpdateModal = false;
        $this->customerId = null;
    }

    public function closeCreateModal()
    {
        $this->resetAll();
    }

    public function openCreateModal()
    {
        $this->resetAll();
        $this->displayCreateModal = true;
    }

    public function openUpdateModal($customerId)
    {
        $this->resetAll();
        $this->customerId = $customerId;
        $this->displayUpdateModal = true;
    }

    public function render()
    {
        $customers = $this->getAllActiveCustomers()->paginate(10);
        //$customers = Customer::paginate(10);

        return view('casemanagement::livewire.customer.view-customers', [
            'customers' => $customers,
        ]);
    }
}
