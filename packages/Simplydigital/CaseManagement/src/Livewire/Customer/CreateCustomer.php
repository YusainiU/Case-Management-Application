<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Enums\CustomerPrimaryTypes;
use Simplydigital\CaseManagement\Enums\CustomerChildTypes;
use Simplydigital\CaseManagement\Models\Customer;

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
    public $address_3;
    public $url;
    public $description;
    public $primaryTypes = [];
    public $childTypes = [];
    public $customers = [];

    protected $rules = [
        'name' => 'required|min:3|max:255',
        'primaryType' => 'required',
        'childType' => 'required_if:type,==,'.CustomerPrimaryTypes::Child_Company->value,
        'address_1' => 'required:min:3|max:255',
        'url' => 'url',        
    ];

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
        $this->validate();
        
        $input = [
            'name' => $this->name,
            'primary_type' => $this->primaryType,
            'child_type' => $this->childType,
            'address_1' => $this->address_1,
            'address_2' => $this->address_2,
            'address_3' => $this->address_3,
            'description' => $this->description,
            'url' => $this->url,
            'active' => true,
        ];

        if($this->parentCompany){
            $input['parent_company'] = $this->parentCompany;
        }

        $new = Customer::create($input);

        return redirect()->route('casemanagement.customer.profile', $new->id);

    }
    public function render()
    {
        return view('casemanagement::livewire.customer.create-customer');
    }
}