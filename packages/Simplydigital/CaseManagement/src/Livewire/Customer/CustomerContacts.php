<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use Livewire\Component;
use Simplydigital\CaseManagement\Services\CaseCustomerServices;
use Livewire\WithPagination;

class CustomerContacts extends Component
{

    use WithPagination;
    public $customerContactId;
    public $customerId;
    public $filter;
    public $sortBy;
    public $sortDirection = 'asc';
    public $showCreateContactModal = false;

    public $listeners = [
        'closeCreateCustomerContactModal',
    ];

    public function closeCreateCustomerContactModal()
    {
        $this->resetAllModals();
    }

    public function resetAllModals()
    {
        $this->showCreateContactModal = false;
    }

    public function openCreateContactModal()
    {
        $this->showCreateContactModal = true;
    }

    public function getCustomerContacts()
    {
        $caseCustomerServices = new CaseCustomerServices();
        return $caseCustomerServices->getCustomerContacts($this->customerId);
    }

    public function getAllCustomerContacts()
    {
        $caseCustomerServices = new CaseCustomerServices();
        return $caseCustomerServices->getAllCustomerContacts();
    }

    public function getCustomerContactDetails()
    {
        $caseCustomerServices = new CaseCustomerServices();
        return $caseCustomerServices->getCustomerContactDetails($this->customerContactId);
    }

    public function render()
    {
        $customerContact = null;
        if($this->filter){
            $caseCustomerServices = new CaseCustomerServices();
            if($this->sortBy){
                $caseCustomerServices->sortByField($this->sortBy, $this->sortDirection);
            }
            $caseCustomerServices->filterResult($this->filter);
        }
        if ($this->customerContactId) {
            $customerContact = $this->getCustomerContactDetails() ?? [];
        }
        if ($this->customerId) {
            $allContacts = $this->getCustomerContacts() ?? [];
        } else {
            $allContacts = $this->getAllCustomerContacts() ?? [];
        }

        return view('casemanagement::livewire.customer.customer-contacts', [
            'customerContacts' => $allContacts->paginate(10),
            'customerContact' => $customerContact,
        ]);
    }
}
