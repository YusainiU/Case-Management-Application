<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use Livewire\Component;
use PSpell\Config;

class CustomerProfile extends Component
{

    public $customerId;
    public $customer;
    public $tabHeaders = [];
    public $selectedTab;

    public function mount()
    {
        $this->getTabHeaders();
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
    }

    public function render()
    {
        return view('casemanagement::livewire.customer.customer-profile');
    }
}