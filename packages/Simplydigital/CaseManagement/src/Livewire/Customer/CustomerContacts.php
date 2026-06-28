<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use Livewire\Component;
use Simplydigital\CaseManagement\Services\CaseCustomerServices;
use Livewire\WithPagination;

class CustomerContacts extends Component
{

    use WithPagination;
    public int|string|null $customerContactId = null;
    public int|string|null $userProfileId = null;
    public int|string|null $customerId = null;
    public ?string $filter = null;
    public ?string $sortBy = null;
    public $sortDirection = 'asc';
    public $showCreateContactModal = false;
    public $showContactProfileModal = false;
    public $showContactUpdateModal = false;

    public $listeners = [
        'closeCreateCustomerContactModal',
        'closeUserProfile' => 'closeProfile',
        'closeUpdateCustomerContactModal' => 'closeUpdateModal',
    ];

    public function closeCreateCustomerContactModal()
    {
        $this->resetAllModals();
    }

    public function closeUpdateModal()
    {
        $this->resetAllModals();
        $this->customerContactId = null;
    }

    public function closeProfile()
    {
        $this->resetAllModals();
        $this->customerContactId = null;
    }    

    public function resetAllModals()
    {
        $this->showCreateContactModal = false;
        $this->showContactProfileModal = false;
        $this->showContactUpdateModal = false;
    }

    public function openCreateContactModal()
    {
        $this->showCreateContactModal = true;
    }

    public function openContactProfileModal(int|string $userId)
    {
        $this->resetAllModals();
        $this->showContactProfileModal = true;
        $this->userProfileId = $userId;
    }

    public function openUpdateContactModal(int|string $contactId)
    {
        $this->resetAllModals();
        $this->showContactUpdateModal = true;
        $this->customerContactId = $contactId;
    }   

    public function getCustomerContacts()
    {
        $caseCustomerServices = new CaseCustomerServices();
        return $caseCustomerServices->getCustomerContacts($this->customerId);
    }

    public function getAllCustomerContacts()
    {
        $utilityService = app('case-customer-services');
        return $utilityService->getAllCustomerContacts();
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
            $utilityService = app('case-customer-services');
            if($this->sortBy){
                $utilityService->sortByField($this->sortBy, $this->sortDirection);
            }
            $utilityService->filterResult($this->filter);
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
