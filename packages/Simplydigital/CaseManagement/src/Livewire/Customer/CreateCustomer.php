<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use Livewire\Attributes\Validate;
use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Enums\CustomerPrimaryTypes;
use Simplydigital\CaseManagement\Enums\CustomerChildTypes;

class CreateCustomer extends ModalComponent
{

    public $customerId;
    public $title = "Create New Customer";
    public $name;
    public $primaryType;
    public $childType;
    public $parentCompany;
    public $address_1;
    public $address_2;
    public $primaryTypes = [];
    public $childTypes = [];
    public $customers = [];
    
    public function mount()
    {
        $this->primaryTypes = CustomerPrimaryTypes::options();
        $this->childTypes = CustomerChildTypes::options();
        $this->customers = $this->getAllActiveCustomers();
    }

    public function getAllActiveCustomers()
    {
        $service = app('case-customer-services');
        return $service->getAllActiveCustomers_id_name();
    }
    
    public function maxWidth(): string
    {
        return '6xl';
    }

    public function closeThisModal()
    {
        $this->dispatch('closeCreateModal');
    }

    public function save()
    {

    }
    public function render()
    {
        return view('casemanagement::livewire.customer.create-customer');
    }
}