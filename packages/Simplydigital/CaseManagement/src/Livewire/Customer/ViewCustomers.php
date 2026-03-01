<?php

namespace Simplydigital\CaseManagement\Livewire\Customer;

use Livewire\Component;
use Livewire\WithPagination;
use Simplydigital\CaseManagement\Models\Customer;

class ViewCustomers extends Component
{
    use WithPagination;

    public $customers;

    public function render()
    {
        $customers = Customer::paginate(10);

        return view('casemanagement::livewire.customer.view-customers', [
            'customers' => $customers,
        ]);
    }
}
