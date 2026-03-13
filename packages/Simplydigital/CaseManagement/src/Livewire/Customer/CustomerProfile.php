<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use Illuminate\Queue\Listener;
use Livewire\Component;
use PSpell\Config;

class CustomerProfile extends Component
{

    public $customerId;
    public $customer;
    public $tabHeaders = [];
    public $selectedTab;
    public $customerAddress;
    public $showUpdateModal = false;
    protected $listeners = [
        'closeCreateModal',
    ];

    public function mount()
    {
        $this->getCustomer();
        $this->getTabHeaders();        
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

    public function resetAllModals()
    {
        $this->showUpdateModal = false;
    }

    public function selectTab($selectedTab)
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

    public function render()
    {
        return view('casemanagement::livewire.customer.customer-profile');
    }
}