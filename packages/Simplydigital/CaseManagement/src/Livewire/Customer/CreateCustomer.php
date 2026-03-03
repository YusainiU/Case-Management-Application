<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use Livewire\Attributes\Validate;
use LivewireUI\Modal\ModalComponent;

class CreateCustomer extends ModalComponent
{

    public $title = "Create New Customer";
    public $name;
    
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

    }
    public function render()
    {
        return view('casemanagement::livewire.customer.create-customer');
    }
}