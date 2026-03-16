<?php

namespace Simplydigital\CaseManagement\Services;

use Simplydigital\CaseManagement\Models\Customer;

class CaseCustomerServices
{

    private $sortBy;
    private $sortDirection = 'asc';
    private $filter;

 
    public function sortByField(string $sortBy, ?string $sortDirection=null)
    {
        $this->sortBy = $sortBy;
        if($sortDirection){
            $this->sortDirection;
        }
    }

    public function filterResult($filter)
    {
        $this->filter = $filter;
    }

    public function getAllActiveCustomers()
    {
        $query = Customer::query();
        $query->where('active',true);

        if($this->filter){
            $filterValue = '%'.$this->filter.'%';
            $query->where(function($q) use ($filterValue){
                $q->orWhere('name','like',$filterValue)
                ->orWhere('primary_type','like', $filterValue)
                ->orWhere('portal_enabled','like', $filterValue)
                ->orWhere('child_type','like', $filterValue)
                ->orWhereHas('parentCompany',function($qq) use($filterValue){
                    $qq->where('name','like', $filterValue);
                });
            });
        }

        if($this->sortBy){
            $query->orderBy($this->sortBy, $this->sortDirection);
        }
        return $query;
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

    public function getCustomer($customerId)
    {
        return Customer::find($customerId);
    }
}