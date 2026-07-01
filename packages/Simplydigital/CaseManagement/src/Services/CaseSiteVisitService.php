<?php

namespace Simplydigital\CaseManagement\Services;

use Simplydigital\CaseManagement\Models\TaskSiteVisit;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class CaseSiteVisitService
{

    protected Builder $query;
    protected ?TaskSiteVisit $supplier = null;
    protected string|int|null $filter = null;
    protected ?string $sortBy = null;
    protected ?string $sortByDirection = 'asc';

    public function __construct()
    {
        $this->query = TaskSiteVisit::query();
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
    
    private function checkIfDate(string $value)
    {
        return \is_string($value) && Carbon::hasFormat($value, 'Y-m-d');
    }
    
    public function buildProfileItems(TaskSiteVisit $visit)
    {
        $items = [];
        foreach ($visit->getAttributes() as $key => $value) {

            if ($value && $this->checkIfDate($value)) {
                $value = Carbon::parse($value)->format('d-m-Y h:i:s');
            }
            if ($key == 'first_visit') {
                $value = $value ? 'Yes' : 'No';
            }
            if($key == 'assigned_to' && $value) {
                $user = $visit->assignedTo->name ?? null;
                $value = $user ?? null;
            }
            if($key == 'scheduled_by' && $value) {
                $user = $visit->scheduledBy->name ?? null;
                $value = $user ?? null;
            }
            if($key == 'active') {
                $value = $value ? 'Yes' : 'No';
            }
            $items[] = [
                'label' => ucfirst(str_replace('_', ' ', $key)),
                'value' => $value,
            ];
        }
        return $items;
    }


    private function buildFilter(?string $filter)
    {
        $filter ??= $this->filter;
        if ($filter) {
            $filterValue = "%$filter%";
            $this->query->where(function ($q) use ($filterValue) {
                $q->orWhere('number', 'like', $filterValue)
                    ->orWhere('short_description', 'like', $filterValue)
                    ->orWhereHas('ticket', function ($qq) use ($filterValue) {
                        $qq->whereHas('customer', function($qqq) use ($filterValue) {
                            $qqq->where('name', 'like', $filterValue);
                        });
                    })
                    ->orWhereHas('assignedTo', function ($qq) use ($filterValue) {
                        $qq->where('name','like',$filterValue);
                    })
                    ->orWhereHas('scheduledBy', function ($qq) use ($filterValue) {
                        $qq->where('name','like',$filterValue);
                    });
            });
        }

        if ($this->sortBy) {
            $this->query->orderBy($this->sortBy, $this->sortByDirection);
        }
    }    

    public function getSiteVisitsForCase(int $caseId, ?string $filter = null)
    {
        $filter ??= $this->filter;
        $this->query->where('ticket_id', $caseId)->where('active', true);
        $this->buildFilter($filter);
        return $this->query;
    }
    
    public function getSiteVisitById(int $visitId)
    {
        return $this->query->find($visitId);
    }

    public function getAllActiveSiteVisits(?string $filter = null)
    {
        $filter ??= $this->filter;
        $this->query->where('active', true);
        $this->buildFilter($filter);
        return $this->query;
    }

    public function getSiteVisitsForCustomer(int $customerId, ?string $filter = null)
    {
        $filter ??= $this->filter;
        $this->query->whereHas('ticket_id', function ($q) use ($customerId) {
            $q->where('customer_id', $customerId);
        })->where('active', true);
        $this->buildFilter($filter);
        return $this->query;
    }


}
