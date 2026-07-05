<?php

namespace Simplydigital\CaseManagement\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Simplydigital\CaseManagement\Models\TaskSiteVisit;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Repositories\CaseRepository;

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

    private function logConfig(array $form): array
    {
        $updater = Auth::user()?->name;
        $assignedName = $this->caseUserServices
            ->getUserById($form['assigned_to'])
            ?->name ?? 'Unassigned';         
        return [
            'visit_scheduled_at' => [
                'set' => [
                    'phrase' => config('case-management.siteVisit.scheduleUpdated'),
                    'tokens' => ['%SCHEDULER%', '%DATETIME%'],
                    'values' => fn ($formatted) => [$updater, $formatted],
                ],
                'cleared' => [
                    'phrase' => config('case-management.siteVisit.scheduleRemoved'),
                    'tokens' => ['%SCHEDULER%'],
                    'values' => fn () => [$updater],
                ],
            ],

            'enroute_at' => [
                'set' => [
                    'phrase' => config('case-management.siteVisit.enrouteUpdated'),
                    'tokens' => ['%ASSIGNEE%', '%DATETIME%', '%USER%'],
                    'values' => fn ($formatted) => [$assignedName, $formatted, $updater],
                ],
                'cleared' => [
                    'phrase' => config('case-management.siteVisit.enrouteRemoved'),
                    'tokens' => ['%ASSIGNEE%', '%USER%'],
                    'values' => fn () => [$assignedName, $updater],
                ],
            ],

            'onsite_at' => [
                'set' => [
                    'phrase' => config('case-management.siteVisit.onsiteUpdated'),
                    'tokens' => ['%ASSIGNEE%', '%DATETIME%', '%USER%'],
                    'values' => fn ($formatted) => [$assignedName, $formatted, $updater],
                ],
                'cleared' => [
                    'phrase' => config('case-management.siteVisit.onsiteRemoved'),
                    'tokens' => ['%ASSIGNEE%', '%USER%'],
                    'values' => fn () => [$assignedName, $updater],
                ],
            ],

            'offsite_at' => [
                'set' => [
                    'phrase' => config('case-management.siteVisit.offsiteUpdate'),
                    'tokens' => ['%ASSIGNEE%', '%DATETIME%', '%USER%'],
                    'values' => fn ($formatted) => [$assignedName, $formatted, $updater],
                ],
                'cleared' => [
                    'phrase' => config('case-management.siteVisit.offsiteRemoved'),
                    'tokens' => ['%ASSIGNEE%', '%USER%'],
                    'values' => fn () => [$assignedName, $updater],
                ],
            ],
        ];
    }

    public function updateVisit(TaskSiteVisit $visit, array $data, array $form): bool
    {
        $case = $visit->ticket;
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
        $logConfig = $this->logConfig($form);

        foreach ($logConfig as $field => $config) {
            if(!$changes[$field]) continue;
            $isCleared = empty($data['field']);
            $entry = $config[$isCleared ? 'cleared' :'set'];
            if ($isCleared) {
                $replaceWith = $entry['values']();
            } else {
                $formatted = Carbon::parse($data[$field])->format('d-m-Y H:i');
                $replaceWith = $entry['values']($formatted);
            }
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
}
