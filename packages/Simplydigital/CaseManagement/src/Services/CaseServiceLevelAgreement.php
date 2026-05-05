<?php

namespace Simplydigital\CaseManagement\Services;

use Simplydigital\CaseManagement\Models\serviceLevelAgreements;
use Illuminate\Database\Eloquent\Builder;

class CaseServiceLevelAgreement
{
    private Builder $query;
    private ?serviceLevelAgreements $sla = null;
    private ?string $sortBy = null;
    private string $sortByDirection = 'asc';
    private string|int|null $filter = null;
    
    public function __construct()
    {
        $this->query = serviceLevelAgreements::query();
        
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
    
    public function buildOptionsForSupplierSelect()
    {
        return serviceLevelAgreements::where('active', true)
            ->orderBy('name', 'asc')
            ->pluck('name', 'id')
            ->toArray();
    }
    
    public function buildProfileItems(serviceLevelAgreements $slas)
    {
        $items = [];
        $utility = app()->make(CaseManagementUtilityServices::class);
        foreach ($slas->getAttributes() as $key => $value) {

            if ($utility::checkIfDate($value)) {
                $value = $utility::displayDateValue($value);
            }
            if ($key == 'start_day') {
                $value = $slas->start_day->label();
            }
            if ($key == 'end_day') {
                $value = $slas->end_day->label();
            }            
            if ($key == 'active') {
                $value = $value ? 'Yes' : 'No';
            }
            if($key == 'type') {
                $value = $slas->type->label();
            }
            if($key == 'service_start_time' || $key == 'service_end_time') {
                $value = $utility->secondsToHMS($value);
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
        if ($this->filter) {
            $filterValue = "%$this->filter%";
            $this->query->where(function ($q) use ($filterValue) {
                $q->orWhere('product_code', 'like', $filterValue)
                    ->orWhere('number', 'like', $filterValue)
                    ->orWhere('short_description', 'like', $filterValue)
                    ->orWhere('name', 'like', $filterValue)
                    ->orWhere('start_day', 'like', $filterValue)
                    ->orWhere('end_day', 'like ', $filterValue);
            });
        }
        if ($this->sortBy) {
            $this->query->orderBy($this->sortBy, $this->sortByDirection);
        }
    }

    public function getActiveSLA()
    {
        $this->query->where('active', true);
        $this->buildFilter();
        return $this->query;
    }

    public function getAllSLAS()
    {
        $this->buildFilter();
        return $this->query;
    }

    public function getSLAById(int $slaId)
    {
        return $this->query->find($slaId);
    }

    public function createSLA(array $input)
    {
        return serviceLevelAgreements::create($input);
    }

    public function updateSLA(serviceLevelAgreements $sla, array $data)
    {
        return $sla->update($data);
    }

}