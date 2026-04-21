<?php

namespace Simplydigital\CaseManagement\Services;

use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\Asset;
class CaseAssetServices
{
    private $customer;
    private $sortBy;
    private $sortDirection = 'asc';
    private $filter;
    
    public function setCustomer($customerId)
    {
        $this->customer = Customer::find($customerId);
    }

    public function sortByField(string $sortBy, ?string $sortDirection = null)
    {
        $this->sortBy = $sortBy;
        if ($sortDirection) {
            $this->sortDirection = $sortDirection;
        }
    }
    
    public function filterResult($filter)
    {
        $this->filter = $filter;
    }    

    public function getActiveAssets()
    {

        $query = Asset::query()->where('active', true);
        if($this->customer) {
            $query->where('customer_id', $this->customer->id);
        }        
        $this->buildFilter($query);        
        return $query;

    }

    public function getAllAssets()
    {
        $query = Asset::query();
        if($this->customer) {
            $query->where('customer_id', $this->customer->id)->get();
        }
        $this->buildFilter($query);
        return $query;
    }    

    private function buildFilter($query)
    {
        if($this->filter) {
            $filterValue = '%' . $this->filter . '%';
            $query->where(function ($q) use ($filterValue) {
                $q->orWhere('short_description', 'like', $filterValue)
                    ->orWhere('number', 'like', $filterValue)
                    ->orWhere('productId', 'like', $filterValue);
            });
        }

        if ($this->sortBy) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        return $query;
    }
    
    public function getAssetById($assetId)
    {
        return Asset::find($assetId);
    }

}