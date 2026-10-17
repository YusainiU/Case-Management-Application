<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use Livewire\Component;
use Simplydigital\CaseManagement\Services\CaseCustomerServices;
use Simplydigital\CaseManagement\Models\CustomerContacts as Contacts;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')] 
class CustomerContacts extends Component
{

    use WithPagination;
    public int|string|null $customerContactId = null;
    public int|string|null $userProfileId = null;
    public int|string|null $customerId = null;
    public ?string $filter = null;
    public ?string $sortBy = null;
    public $sortDirection = 'asc';
    public ?string $sortField = null;    
    public $showCreateContactModal = false;
    public $showContactProfileModal = false;
    public $showContactUpdateModal = false;

    public $listeners = [
        'closeCreateCustomerContactModal',
        'closeUserProfile' => 'closeProfile',
        'closeUpdateCustomerContactModal' => 'closeUpdateModal',
    ];

    public function mount()
    {
        $this->authorize('viewAny', Contacts::class);
    }

    public function sortByField(string $fieldName, string $direction)
    {
        if($this->sortField == $fieldName)
        {
            $this->sortField = null;
        }else{
            $this->sortField = $fieldName;
            $this->sortDirection = $direction;
        }
        $this->getAllCustomerContacts();
    }    

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
        if($this->sortField){  
            $utilityService->sortByField($this->sortField, $this->sortDirection);
        }            
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
