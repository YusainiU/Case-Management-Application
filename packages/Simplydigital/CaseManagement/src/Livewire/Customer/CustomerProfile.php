<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use Livewire\Component;
use Simplydigital\CaseManagement\Models\Customer;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class CustomerProfile extends Component
{

    public int|string|null $customerId = null;
    public ?customer $customer = null;
    public $tabHeaders = [];
    public ?string $selectedTab = null;
    PUBLIC int|string|null $customerIdToDelete = null;
    public ?string $customerAddress = null;
    public $showUpdateModal = false;
    public $showConfirmDelete = false;
    public ?string $navigateToComponent = null;
    protected $listeners = [
        'closeCreateModal',
        'closeDeleteModal',
        'closeDeleteAndRedirect',
    ];

    public function mount()
    {
        $this->authorize('viewAny', Customer::class);
        $this->getCustomer();
        $this->getTabHeaders();        
    }

    public function navigateTo(string $componentName)
    {
        if($componentName === 'parentCompany'){
            $this->redirectRoute('casemanagement.customer.profile', ['customerId' => $this->customer->parentCompany->id]);
        }
    }

    public function displayUpdate()
    {
        $this->resetAllModals();
        $this->showUpdateModal = true;
    }

    public function closeCreateModal()
    {
        $this->resetAllModals();
    }

    public function closeDeleteModal()
    {
        $this->resetAllModals();
        
    }

    public function closeDeleteAndRedirect()
    {
        $this->resetAllModals();
        $this->redirectRoute('casemanagement.customers.view');
    }

    public function confirmDelete()
    {
        $this->resetAllModals();
        $this->showConfirmDelete = true;
    }

    public function resetAllModals()
    {
        $this->showUpdateModal = false;
        $this->showConfirmDelete = false;
    }

    public function selectTab(string $selectedTab)
    {
        $this->selectedTab = $selectedTab;
    }

    public function getTabHeaders()
    {
        $this->tabHeaders = Config('case-management.customer_profile_tabs');
        $this->selectedTab = $this->tabHeaders['outstandingCases'];
    }

    public function resetTab()
    {
        $this->selectedTab = null;
    }

    public function getCustomer()
    {
        $utilityService = app('case-customer-services');
        $this->customer = $utilityService->getCustomer($this->customerId);
        $addr = $this->customer->getAddress();
        if($addr){
            $this->customerAddress = implode(', ', $this->customer->getAddress());
        }
    }

    public function viewCustomerList()
    {
        $this->redirectRoute('casemanagement.customers.view',['customerId' => $this->customerId]);
    }    

    public function render()
    {
        return view('casemanagement::livewire.customer.customer-profile');
    }
}