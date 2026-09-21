<?php


namespace Simplydigital\CaseManagement\Livewire\Customer;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Services\CaseCustomerServices;

class CustomerSummary extends ModalComponent
{
    public string $title = 'Customer Summary';
    public int $customerId;
    public Customer $customer;
    public array $customerItems = [];
    
    public function mount(int $customerId)
    {
        $this->authorize('viewAny', Customer::class);
        $this->customerId = $customerId;
        $this->loadCustomer();
    }

    protected function customerService(): CaseCustomerServices
    {
        return app(CaseCustomerServices::class);
    }    
    
    public function loadCustomer()
    {
        $service = $this->customerService();
        $this->customer = $service->getCustomer($this->customerId);
        $this->customerItems = $service->buildProfileItems($this->customer);
    }

    public function closeThisModal()
    {
        $this->dispatch('closeProfileModal');   
    }
    
    public function render()
    {
        return view('casemanagement::livewire.customer.customer-summary');
    }


}