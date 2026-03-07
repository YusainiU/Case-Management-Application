<?php

namespace Simplydigital\CaseManagement\Services;

use Simplydigital\CaseManagement\Models\Customer;

class CaseCustomerServices
{
    public function getAllActiveCustomers()
    {
        return Customer::where('active',true)->get();
    }

    public function getAllActiveCustomers_id_name()
    {
        $c = Customer::select('id','name')
            ->where('active',true)
            ->get()
            ->sortBy('name')
            ->toArray();
        return array_values($c);
    }
}