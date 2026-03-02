<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use Livewire\Attributes\Validate;
use LivewireUI\Modal\ModalComponent;

class CreateCustomer extends ModalComponent
{

    public function maxWidth(): string
    {
        return '6xl';
    }

    public function save()
    {

    }
    public function render()
    {
        return view('casemanagement::livewire.customer.create-customer');
    }
}