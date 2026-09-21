<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Models\Customer;

class DeleteCustomer extends ModalComponent
{

    public int|string|null $customerId = null;
    public ?customer $customer = null;
    public ?string $deleteText = null;

    public function mount()
    {        
        $this->getCustomer();
        $this->deleteText = "Delete For Customer has not been built yet";
        $this->authorize('delete', $this->customer);
    }

    public function getCustomer()
    {
        $utilityService = app('case-customer-services');
        $this->customer = $utilityService->getCustomer($this->customerId);        
    }
    
    public function deleteCustomer()
    {
        $this->authorize('delete', $this->customer);
        $this->dispatch('closeDeleteAndRedirect');
    }

    public function closeDeleteModal()
    {
        $this->dispatch('closeDeleteModal');
    }

    public function render()
    {
        return view('casemanagement::livewire.customer.delete-customer');
    }
}