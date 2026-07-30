<?php

namespace Simplydigital\CaseManagement\Services;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\slaTask;
use Simplydigital\CaseManagement\Models\TaskSiteVisit;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Repositories\CaseRepository;
use Simplydigital\CaseManagement\Services\CaseLogAdministrationService;
use Simplydigital\CaseManagement\Services\SLACalculationService;
use Simplydigital\CaseManagement\Services\CaseManagementUtilityServices;

class CaseTicketServices
{
    protected ?Builder $query = null;

    private ?string $sortBy = null;

    private string $sortDirection = 'asc';

    public string|int|null $filter = null;

    private CaseTicketUtilityServices $caseTicketUtilityServices;
    private CaseRepository $caseRepository; 
    private CaseLogAdministrationService $caseLogService;
    private SLACalculationService $calcService;


    public function __construct()
    {
        $this->query = tickets::query();
        $this->caseTicketUtilityServices = app()->make(CaseTicketUtilityServices::class);
        $this->caseRepository = app()->make(CaseRepository::class);
        $this->caseLogService = app()->make(CaseLogAdministrationService::class);
        $this->calcService = app(SLACalculationService::class);
    }

    public function setCustomer(int $customerId)
    {
        $this->query->where('customer_id', $customerId);
    }

    public function getAllOpenCases(?string $filter)
    {
        $this->query->where('closed_datetime', '=', null);
        if($filter){
            $this->buildFilter($filter);    
        }
        return $this->query;
    }

    public function getOutstandingCases(?string $filter)
    {
        $this->query
            ->where('closed_datetime', '=', null)
            ->where('resolved_datetime', '=', null);
        if($filter){
            $this->buildFilter($filter);    
        }        
        return $this->query;
    }

    public function getAllClosedCases(?string $filter)
    {
        $this->query->where('closed_datetime', '!=', null);
        if($filter){
            $this->buildFilter($filter);    
        }        
        return $this->query;
    }

    public function getAllOpenResolvedCases(?string $filter)
    {
        $this->query
            ->where('resolved_datetime', '!=', null)
            ->where('closed_datetime', '=', null);
        if($filter){
            $this->buildFilter($filter);    
        }
        return $this->query;
    }

    public function getAllClosedResolvedCases(?string $filter)
    {
        $this->query
            ->where('resolved_datetime', '!=', null)
            ->where('closed_datetime', '!=', null);
        if($filter){
            $this->buildFilter($filter);    
        }
        return $this->query;
    }

    public function getAllPausedCases(?string $filter)
    {
        $this->query->where('state', '=', CaseState::Paused->value)
            ->where('closed_datetime', '=', null);
        if($filter){
            $this->buildFilter($filter);    
        }
        return $this->query;
    }

    public function getAllBreachedCases(?string $filter)
    {
        $this->query
            ->whereNull('closed_datetime')
            ->whereHas('slaTasks', function($r) {
                $r->breached();
            });
        if($filter){
            $this->buildFilter($filter);
        }
        return $this->query;
    }

    public function getAllNearBreachCases(?string $filter)
    {
        $this->query
            ->whereNull('closed_datetime')
            ->whereHas('slaTasks', function($r) {
                $r->showWarningFlag();
            });
        if($filter) {
            $this->buildFilter($filter);
        }
        return $this->query;
    }

    public function getAllUnassignedCases(?string $filter)
    {
        $this->query
            ->whereNull('closed_datetime')
            ->whereNull('assigned_to');
        if($filter) {
            $this->buildFilter($filter);
        }
        return $this->query;
    }
    
    public function getAllNoUpdateCases(?string $filter)
    {
        $this->query
            ->whereNull('closed_datetime')
            ->whereDoesntHave('taskLogs');
        if($filter) {
            $this->buildFilter($filter);
        }
        return $this->query;                     
    }

    public function getCaseById(int $caseId)
    {
        return $this->query->find($caseId);
    }

    public function getCasesByCustomerId(int $customerId, ?string $filter)
    {
        $this->query->where('customer_id');
        if($filter){
            $this->buildFilter($filter);    
        }
        return $this->query;
    }

    public function buildSummaryItems(tickets $ticket)
    {
        $items = [];
        foreach ($ticket->getAttributes() as $key => $value) {
            if ($key === 'customer_id' && $value) {
                $value = $ticket->customer?->name;
            } elseif ($value && CaseManagementUtilityServices::checkIfDate($value)) {
                $value = CaseManagementUtilityServices::displayDateValue($value);
            } elseif ($value && ($key === 'assigned_to' || $key == 'raised_by_user' || $key == 'resolved_by' || $key == 'created_by')) {
                $value = match ($key) {
                    'assigned_to' => $ticket->assignedTo?->name,
                    'raised_by_user' => $ticket->raisedByUser?->customerUser?->name,
                    'resolved_by' => $ticket->resolvedBy?->name,
                    'created_by' => $ticket->createdBy?->name,
                    default => $value,
                };
            } elseif ($key === 'sla_applications_id' && $value) {
                $value = $ticket->slaApplication->name;
            } elseif ($key === 'category' && $value) {
                $value = $ticket->category->label();
            } elseif ($key === 'source' && $value) {
                $value = $ticket->source->label();
            } elseif ($key === 'state' && $value) {
                $value = $ticket->state;
            } elseif ($key === 'list_of_assets' && $value) {
                $value = is_array($value) ? $value : json_decode($value, true);
                $value = CaseAssetServices::getAssetsFromArrayOfIds($value)->pluck('short_description')->toArray();
                $value = implode(', ', $value);
            } elseif ($key === 'type' && $value) {
                $value = $ticket->type->label();
            } else {
                $value ??= 'N/A';
            }
            $items[] = [
                'label' => ucfirst(str_replace('_', ' ', $key)),
                'value' => $value,
            ];
        }

        $sla = $this->buildTicketSLASummary($ticket);
        $summary = array_merge($items, $sla);

        return $summary;
    }

    public function buildTicketSLASummary(tickets $ticket)
    {
        $items = [];

        if ($ticket?->slaTasks) {

            foreach ($ticket->slaTasks->getAttributes() as $key => $value) {
                $items[] = [
                    'label' => ucfirst(str_replace('_', ' ', $key)),
                    'value' => $value,
                ];
            }

        }

        return $items;

    }

    private function buildFilter(string $filter)
    {

        if ($filter) {
            $filterValue = "%$filter%";
            $this->query->where(function ($q) use ($filterValue) {
                $q->orWhere('number', 'like', $filterValue)
                    ->orWhere('short_description', 'like', $filterValue)
                    ->orWhere('description', 'like', $filterValue)
                    ->orWhereHas('customer', function ($qq) use ($filterValue) {
                        $qq->where('name', 'like', $filterValue);
                    })
                    ->orWhereHas('assignedToUser', function ($qq) use ($filterValue) {
                        $qq->where('name','like',$filterValue);
                    });                    
            });
        }

        if ($this->sortBy) {
            $this->query->orderBy($this->sortBy, $this->sortDirection);
        }
    }

    public function sortByField(string $sortBy, ?string $sortDirection = null)
    {
        $this->sortBy = $sortBy;
        if ($sortDirection) {
            $this->sortDirection = $sortDirection;
        }
    }

    public function filterResult(string|int $filter)
    {
        $this->filter = $filter;
    }

    public function createNewTicket(array $data)
    {
        return $this->caseTicketUtilityServices->createNewTicket($data);
    }

    public function reopenTicket(tickets $ticket)
    {
        return $this->caseTicketUtilityServices->reopenTicket($ticket);
    }

    public function retrieveTicketStatus(tickets $ticket)
    {
        return $this->caseTicketUtilityServices->retrieveTicketStatus($ticket->id);
    }

    public function setSiteVisit(tickets $ticket, array $data)
    {
        return $this->caseTicketUtilityServices->setSiteVisit($ticket, $data);
    }

    public function updateSiteVisit(TaskSiteVisit $siteVisit, array $data)
    {
        return $this->caseTicketUtilityServices->updateSiteVisit($siteVisit, $data);
    }

    public function setAssignedTo(tickets $ticket, string|int|null $assigned_to)
    {
        return $this->caseTicketUtilityServices->setAssignedTo($ticket, $assigned_to);
    }

    public function unassignedUser(tickets $ticket)
    {
        return $this->caseTicketUtilityServices->unassignedUser($ticket);
    }

    public function slaBreachUpdate(slaTask $tasks)
    {
        $this->caseTicketUtilityServices->slaBreachUpdate($tasks);
    }

    public function updateCaseSLADetails(tickets $ticket, array $data)
    {
        /*
            $data  = [
                "responded_at"
                "fixed_at"
                "assigned_to"
                "log"
            ]
        */

        $result = [];
        $respondedAt = $data['responded_at'] ?? null;
        $fixedAt = $data['fixed_at'] ?? null;
        $assignedTo = $data['assigned_to'] ?? null;
        $log = trim($data['log']) ?? null;

        $savedRespondedAt = $this->caseTicketUtilityServices->updateRespondAt($ticket, $respondedAt);
        $savedFixedAt = $this->caseTicketUtilityServices->updateFixedAt($ticket, $fixedAt);
        $savedAssignedTo = $this->setAssignedTo($ticket, $assignedTo);
        $savedLog = $this->caseTicketUtilityServices->buildProgressLog($ticket, $log);
        
        $savedRespondedAt && $result[] = 'Responded At';
        $savedAssignedTo && $result[] = 'Assigned To';
        $savedFixedAt && $result[] = 'Fixed At';
        $savedLog && $result[] = 'Progress Log';

        return $result;
        
    }

    public function createSlaTask(tickets $ticket)
    {
        $this->caseTicketUtilityServices->createSlaTask($ticket);
    }

    public function pauseSla(tickets $ticket)
    {
        $this->caseTicketUtilityServices->pauseCaseSla($ticket);
    }

    public function resumeSla(tickets $ticket)
    {
        $this->caseTicketUtilityServices->resumeCaseSla($ticket);
    }

    public function isTodayBusinessWorkingDay(tickets $ticket)
    {
        $data = $this->calcService->createSlaData($ticket);
        return $this->calcService->isBusinessWorkingHours($data, Carbon::now());
    }

    public function isNowInsideServiceHours(tickets $ticket)
    {
        return CaseManagementUtilityServices::isNowInsideSLA($ticket);
    }

    public function getAllCaseSlaPauses(tickets $ticket)
    {
        $pauses = $ticket->slaTasks?->pauses;
        return $pauses;
    }

    public function cancelPauseSla(tickets $ticket)
    {
        $this->caseTicketUtilityServices->cancelPauseSla($ticket);
    }

    public function closeCase(tickets $ticket, array $data)
    {
        return $this->caseTicketUtilityServices->closeTicket($ticket, $data);
    }

    public function resolveCase(tickets $ticket, array $data)
    {
        return $this->caseTicketUtilityServices->resolveCase($ticket, $data);
    }
    
}