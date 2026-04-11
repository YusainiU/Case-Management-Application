<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Enums\CustomerContactRoles;

class UpdateCustomerContact extends ModalComponent
{

    public $title = 'Update Customer Contact';
    public $confirmText = 'Update';
    public $customerContactId;
    public $customerContact;
    public $contactRole;
    public $customers = [];
    public $contactRoles = [];
    public $customerId;
    public $description;
    public $active = true;
    public $hasPortalAccess = false;

    public function rules()
    {
        return [
            'customerId' => 'required|exists:customers,id',
            'contactRole' => 'required|in:' . implode(',', array_column(CustomerContactRoles::cases(), 'value')),
            'description' => 'nullable|string',
            'active' => 'boolean',
            'hasPortalAccess' => 'boolean',
        ];
    }

    public function mount()
    {
        $this->loadCustomerContact();
        $this->loadCustomers();
        $this->contactRoles = CustomerContactRoles::options();
    }

    public function closeThisModal()
    {
        $this->dispatch('closeUpdateCustomerContactModal');
    }
    
    public function loadCustomerContact()
    {
        $caseCustomerServices = app('case-customer-services');
        $this->customerContact = $caseCustomerServices->getCustomerContactById($this->customerContactId);
        if ($this->customerContact) {
            $this->customerId = $this->customerContact->customer_id;
            $this->contactRole = $this->customerContact->customerRole?->value;
            $this->description = $this->customerContact->description;
            $this->active = $this->customerContact->active;
            $this->hasPortalAccess = $this->customerContact->has_portal_access;
        }
    }

    public function loadCustomers()
    {
        $caseCustomerServices = app('case-customer-services');
        $this->customers = $caseCustomerServices->getAllActiveCustomers_id_name();
    }

    public function save()
    {
        $this->validate();

        $inputData = [
            'customer_id' => $this->customerId,
            'customerRole' => $this->contactRole,
            'description' => $this->description,
            'active' => $this->active,
            'has_portal_access' => $this->hasPortalAccess,
        ];

        $caseCustomerServices = app('case-customer-services');
        $caseCustomerServices->updateCustomerContactOnly($this->customerContactId, $inputData);

        $this->dispatch('closeUpdateCustomerContactModal');
    }

    public function render()
    {
        return view('casemanagement::livewire.customer.update-customer-contact');
    }

}   
