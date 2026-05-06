<?php

namespace Simplydigital\CaseManagement\Services;

use Simplydigital\CaseManagement\Models\slaApplication;
use Simplydigital\CaseManagement\Models\Customer;
use Illuminate\Database\Eloquent\Builder;

class CaseSLAPService
{

    private ?Builder $query = null;
    private ?Customer $customer = null;
    private ?string $sortBy = null;
    private string $sortByDirection = 'asc';
    private string|int|null $filter = null;

    public function __construct()
    {
        $this->query = slaApplication::query();
    }

    public function setCustomer(int $customerId)
    {
        $this->customer = Customer::find($customerId);
    }

    public function filterResult(string|int $filter)
    {
        $this->filter = $filter;
    }

    public function sortByField(string $field, ?string $direction = null)
    {

        $this->sortBy = $field;
        $this->sortByDirection = $direction ?? 'asc';
    }
    
    public function buildOptionsForSLAPSelect()
    {
        if($this->customer) {
            $opts = $this->customer;
            $opts->where('active', true);
        }else{
            $opts = slaApplication::where('active', true);
        }
        
        $opts->orderBy('name', 'asc')
        ->pluck('name', 'id')
        ->toArray();

        return $opts;
    }
    
    public function buildProfileItems(slaApplication $slap)
    {
        $items = [];
        $utility = app()->make(CaseManagementUtilityServices::class);
        foreach ($slap->getAttributes() as $key => $value) {

            if ($utility::checkIfDate($value)) {
                $value = $utility::displayDateValue($value);
            }
            if($key == 'customer_id') {
                $value = $slap->customer->name;
            }
            if($key == 'service_level_agreement_id') {
                $value = $slap->serviceLevelAgreement->name;
            }
            $items[] = [
                'label' => ucfirst(str_replace('_', ' ', $key)),
                'value' => $value,
            ];
        }
        return $items;
    }
    
    public function buildFilter()
    {
        if($this->customer) {
            $this->query->where('customer_id', $this->customer->id);
        }

        if ($this->filter) {
            $filterValue = "%$this->filter%";
            $this->query->where(function ($q) use ($filterValue) {
                $q->orWhere('product_code', 'like', $filterValue)
                    ->orWhere('number', 'like', $filterValue)
                    ->orWhere('short_description', 'like', $filterValue)
                    ->orWhere('priority', 'like', $filterValue)
                    ->orWhereHas('customer',function($q) use ($filterValue) {
                        $q->where('name', 'like', $filterValue);
                    })
                    ->orWhereHas('serviceLevelAgreement', function($qq) use ($filterValue) {
                        $qq->where('name', 'like', $filterValue);
                    });
            });
        }
        if ($this->sortBy) {
            $this->query->orderBy($this->sortBy, $this->sortByDirection);
        }
    }
    
    public function getActiveSLAP()
    {
        $this->query->where('active', true);
        $this->buildFilter();
        return $this->query;
    }

    public function getAllSLAP()
    {
        $this->buildFilter();
        return $this->query;
    }

    public function getSLAPById(int $slapId)
    {
        return $this->query->find($slapId);
    }    

}