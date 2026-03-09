<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use Livewire\Component;

class CustomerProfile extends Component
{

    public $customerId;
    public $customer;

    public function mount()
    {

    }

    public function getCustomer()
    {
        $utilityService = app('case-customer-services');
        $this->customer = $utilityService->getCustomer($this->customerId);
    }

    public function render()
    {
        return view('casemanagement::livewire.customer.customer-profile');
    }
}