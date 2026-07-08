<?php

namespace Simplydigital\CaseManagement\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Simplydigital\CaseManagement\Models\TaskSiteVisit;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Repositories\CaseRepository;
use Simplydigital\CaseManagement\Enums\CaseState;

class CaseSiteVisitService
{
    protected Builder $query;

    protected ?TaskSiteVisit $supplier = null;

    protected string|int|null $filter = null;

    protected ?string $sortBy = null;

    protected ?string $sortByDirection = 'asc';

    private ?CaseRepository $repo;

    private ?CaseUserServices $caseUserServices;

    private ?CaseLogAdministrationService $logService;

    public function __construct()
    {
        $this->query = TaskSiteVisit::query();
        $this->caseUserServices = app(CaseUserServices::class);
        $this->repo = app(CaseRepository::class);
        $this->logService = app(CaseLogAdministrationService::class);
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
            if ($key == 'assigned_to' && $value) {
                $user = $visit->assignedTo->name ?? null;
                $value = $user ?? null;
            }
            if ($key == 'scheduled_by' && $value) {
                $user = $visit->scheduledBy->name ?? null;
                $value = $user ?? null;
            }
            if ($key == 'active') {
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
                        $qq->whereHas('customer', function ($qqq) use ($filterValue) {
                            $qqq->where('name', 'like', $filterValue);
                        });
                    })
                    ->orWhereHas('assignedTo', function ($qq) use ($filterValue) {
                        $qq->where('name', 'like', $filterValue);
                    })
                    ->orWhereHas('scheduledBy', function ($qq) use ($filterValue) {
                        $qq->where('name', 'like', $filterValue);
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
        $this->query->where('ticket_id', $caseId);
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

    public function updateVisit(TaskSiteVisit $visit, array $data, array $form): bool
    {
        $case = $visit->ticket;
        $old = clone $visit;
        $changes = $this->repo->trackChangesOnVisit($visit, $form);
        foreach (['enroute_at', 'onsite_at', 'offsite_at'] as $field) {
            if (\array_key_exists($field, $form)) {
                $data[$field] = $form[$field];
            }
        }
        if (! $visit->update($data)) {
            return false;
        }

        $updater = Auth::user()?->name;
        $type = config('case-management.logs.type.site_visit');
        $logConfig = $this->logService->visitLogConfig($old, $form);

        foreach ($logConfig as $field => $config) {
            if(!$changes[$field] ?? false) continue;
            $isCleared = blank($data[$field] ?? null);
            $entry = $config[$isCleared ? 'cleared' :'set'];
            $type = $entry['type'];
            $isDate = $entry['dateFormat'];
            $formatted = $isDate ? Carbon::parse($data[$field])->format('d-m-Y H:i') : null;
            $replaceWith = $entry['values']($formatted);            
            $logText = $this->logService->buildVisitUpdateLog(
                $entry['phrase'],
                $entry['tokens'],
                $replaceWith
            );
            $this->logService->createSystemLog($case, $logText, $type);                        
        }

        return true;
    }

    public function createVisit(tickets $case, array $data)
    {
        $firstVisit = $case->siteVisits->isEmpty() ?? false;
        $scheduled_by = Auth::user()->id;
        $scheduled_by_name = Auth::user()?->name;
        $data['scheduled_by'] = $scheduled_by;
        $data['first_visit'] = $firstVisit;
        $created = $this->repo->createVisit($data);
        if ($created) {

            //Changethe ticket state to progress
            $caseState = CaseState::Site_Visit;
            $this->repo->updateCaseState($case, $caseState);

            // Create Log
            $scheduled_at = Carbon::parse($data['visit_scheduled_at'])->format('d-m-Y H:i');
            $assigned = $data['assigned_to'];
            $assignedDetails = $assigned ? $this->caseUserServices->getUserById($assigned) : null;
            $assignedName = $assignedDetails->name ?? 'No Assignment';
            $type = Config('case-management.logs.type.site_visit');
            if ($firstVisit) {
                $type = Config('case-management.logs.type.site_visit_first');
            }
            $logText = $this->logService->buildVisitLog($assignedName, $scheduled_by_name, $scheduled_at);
            $this->logService->createSystemLog($case, $logText, $type);
        }

        return $created;
    }

    public function canComplete(TaskSiteVisit $visit)
    {
        return  $visit->visit_scheduled_at !== null &&
                $visit->enroute_at !== null &&
                $visit->onsite_at !== null &&
                $visit->offsite_at !== null;
    }

    public function completeVisit(TaskSiteVisit $visit)
    {
        $data = ['active' => false];
        $case = $visit->ticket;
        $updated = $this->repo->updateVisit($visit, $data);
        if($updated) {

            //Change case state if there is no outstanding visit
            $visitExists = $case->outstandingVisit()->exists();
            if(!$visitExists){
                $this->repo->updateCaseState($case, CaseState::In_Progress);
            }

            $type = config('case-management.logs.type.completed');
            $logText = $this->logService->buildVisitCompletionLog();
            $this->logService->createSystemLog($visit->ticket, $logText, $type);
        }

        return $updated;
    }

    public function cancelAndRemoveVisit(TaskSiteVisit $visit)
    {
        $shortDescription = $visit->short_description;
        $number = $visit->number;
        $case = $visit->ticket;
        $remover = Auth::user()?->name;
        $deleted = $this->repo->deleteVisit($visit);
        if($deleted){

            //Changed case state
            $visitExists = $case->outstandingVisit()->exists();
            if(!$visitExists) {
                $this->repo->updateCaseState($case, CaseState::In_Progress);
            }

            //Create Log
            $text = "$number - $shortDescription";
            $logText = $this->logService->buildVisitDeletion($shortDescription, $remover);
            $type = Config('case-management.logs.type.site_visit');
            $this->logService->createSystemLog($case, $logText, $type);
        }
        return $deleted;
    }
}
