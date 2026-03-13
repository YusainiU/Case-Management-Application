<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use Livewire\Component;
use Livewire\WithPagination;
use Simplydigital\CaseManagement\Models\Customer;

class ViewCustomers extends Component
{
    use WithPagination;

    public $activeCustomers;
    public $displayCreateModal = false;

    protected $listeners = ['closeCreateModal'];

    public function mount()
    {
        $this->getAllActiveCustomers();
    }

    public function getAllActiveCustomers()
    {
        $utilityService = app('case-customer-services');
        $this->activeCustomers = $utilityService->getAllActiveCustomers();        
    }

    public function resetAll()
    {
        $this->displayCreateModal = false;
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

    public function render()
    {
        $customers = Customer::paginate(10);

        return view('casemanagement::livewire.customer.view-customers', [
            'customers' => $customers,
        ]);
    }
}
