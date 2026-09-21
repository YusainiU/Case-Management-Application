<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Enums\CustomerPrimaryTypes;
use Simplydigital\CaseManagement\Enums\CustomerChildTypes;
use Simplydigital\CaseManagement\Models\Customer;

class CreateCustomer extends ModalComponent
{

    public ?int $customerId = null;
    public ?Customer $customer = null;
    public ?string $title = "Create New Customer";
    public ?string $confirmText = "Create";
    public ?string $name;
    public ?string $primaryType = null;
    public ?string $childType = null;
    public ?string $parentCompany = null;
    public ?string $address_1 = null;
    public ?string $address_2 = null;
    public ?string $address_3 = null;
    public ?string $telephone_1 = null;
    public ?string $url = null;
    public ?string $description = null;
    public bool $active = true;
    public ?bool $portalEnabled = false;
    public array $primaryTypes = [];
    public array $childTypes = [];
    public array $customers = [];

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
        if($this->customerId){
            $this->getCustomer();
            $this->authorize('update', $this->customers);
        }else{
            $this->authorize('create', Customer::class);
        }
    }

    public function getCustomer()
    {
        $service = app('case-customer-services');
        $this->customer = $service->getCustomer($this->customerId);
        if($this->customer){
            $this->retrieveCustomerDetails();
            $this->title = "Update Customer Details";
            $this->confirmText = "Update";
        }
    }

    public function retrieveCustomerDetails()
    {
        $this->name = $this->customer->name;
        $this->primaryType = $this->customer->primary_type?->value;
        $this->childType = $this->customer->child_type?->value;
        $this->parentCompany = $this->customer->    parent_company;
        $this->address_1 = $this->customer->address_1;
        $this->address_2 = $this->customer->address_2;
        $this->address_3 = $this->customer->address_3;
        $this->telephone_1 = $this->customer->telephone_1;
        $this->url = $this->customer->url;
        $this->description = $this->customer->short_description;
        $this->active = $this->customer->active;
        $this->portalEnabled = $this->customer->portalEnabled;
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
            'telephone_1' => $this->telephone_1,
            'short_description' => $this->description,
            'url' => $this->url,
            'active' => $this->active,
            'portal_enabled' => $this->portalEnabled,
        ];

        if($this->parentCompany){
            $input['parent_company'] = $this->parentCompany;
        }

        if($this->customerId){
            $this->authorize('update', $this->customer);
            $this->customer->update($input);
            $this->closeThisModal();
        }else{
            $this->authorize('create', Customer::class);
            $new = Customer::create($input);
            return redirect()->route('casemanagement.customer.profile', $new->id);
        }
        
    }

    public function render()
    {
        return view('casemanagement::livewire.customer.create-customer');
    }
}