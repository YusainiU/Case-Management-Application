<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Enums\CustomerContactRoles;

class CreateCustomerContact extends ModalComponent
{
 
    public $customerId;
    public $customerContactId;
    public $contact;
    public $customers = [];
    public $customer;
    public $contactRoles;
    public $contactRole;
    public $isUpdate = false;
    public $title = 'Create Customer Contact';
    public $confirmText = 'Create';
    public $name;
    public $email;
    public $address_1;
    public $address_2;
    public $county;
    public $postcode;
    public $country='United Kingdom';
    public $phone;
    public $description;
    public $active = true;
    public $hasPortalAccess = false;

    public function mount()
    {
        $this->customers = $this->getAllActiveCustomers();
        $this->contactRoles = CustomerContactRoles::options();
        if ($this->customerContactId) {
            $this->getCustomerContact();
            $this->isUpdate = true;
            $this->title = 'Update Customer Contact';
            $this->confirmText = 'Update';
        }
        if($this->customerId) {
            $this->customer = $this->customerId;
        }
    }
    public function resetAll()
    {
        $this->customerContactId = null;
        $this->contact = null;
        $this->isUpdate = false;
    }

    public function getCustomerContact()
    {
        $service = app('case-customer-services');
        $this->contact = $service->getCustomerContactDetails($this->customerContactId);
    }

    public function closeThisModal()
    {
        $this->resetAll();
        $this->dispatch('closeCreateCustomerContactModal');
    }

    public function getAllActiveCustomers()
    {
        $service = app('case-customer-services');
        return $service->getAllActiveCustomers_id_name();
    }    

    public function rules()
    {
        return [
            'customer' => 'required',
            'contactRole' => 'required',
            'name' => 'required',
            'email' => 'required|email',
            'address_1' => 'required',
            'address_2' => 'nullable',
            'county' => 'nullable',
            'postcode' => 'required',
            'country' => 'nullable',
            'phone' => 'nullable',
            'description' => 'nullable',
        ];
    }

    public function save()
    {
        $this->validate();
        $service = app('case-customer-services');
        $input = [
            'customer_id' => $this->customer,
            'name' => $this->name,
            'email' => $this->email,
            'address_1' => $this->address_1,
            'address_2' => $this->address_2,
            'county' => $this->county,
            'postcode' => $this->postcode,
            'country' => $this->country,
            'phone' => $this->phone,
            'description' => $this->description,
            'active' => $this->active,
            'has_portal_access' => $this->hasPortalAccess,
            'contact_role' => $this->contactRole,
        ];
        if ($this->isUpdate) {
            $service->updateCustomerContact($this->customerContactId, $input);                        
        } else {
            $service->createCustomerContact($input);            
        }
        $this->resetAll();
        $this->dispatch('closeCreateCustomerContactModal');

    }
    public function render()
    {
        return view('casemanagement::livewire.customer.create-customer-contact');
    }
}