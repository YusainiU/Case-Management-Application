<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseTicketServices;

#[Layout('components.layouts.app')]
class ViewCases extends Component
{
    use WithPagination;

    public ?int $customerId = null;

    public ?int $caseId = null;

    public ?Collection $tickets = null;

    public ?tickets $ticket = null;

    public ?Customer $customer = null;

    public ?string $filter = null;

    public ?string $sortField = null;

    public ?string $sortDirection = null;

    public bool $displayCreateModal = false;

    public bool $displayUpdateModal = false;

    public bool $displayProfileModal = false;

    public bool $displaySummaryModal = false;

    public bool $displayDescription = false;

    public bool $displaySlaStatus = false;

    public array $descriptionDetails = [];

    public ?string $displayCaseStatus = null;

    public bool $displayCustomerModal = false;

    public string $listTitle = CaseState::Open->value;

    public string $viewMode = 'table';

    #[Url]
    public ?string $dashboardFilter = null;

    /**
     * @var array<string, string>
     */
    protected $listeners = [
        'closeCreateCaseModal' => 'closeCreateModal',
        'closeUpdateCaseModal' => 'closeUpdateModal',
        'closeProfileModal' => 'closeProfileModal',
        'closeSummaryModal' => 'closeSummaryModal',
        'reloadCaseStatus' => '$refresh',
        'AllClosedCases' => 'openAllClosedCases',
        'AllPausedCases' => 'openAllPausedCases',
        'ResetFilterSelection' => 'resetFilterSelection',
        'AllResolvedCases' => 'openAllResolvedCases',
        'AllNearBreachedCases' => 'openAllNearBreachedCases',
        'AllBreachedCases' => 'openAllBreachedCases',
        'AllUnassignedCases' => 'openAllUnassignedCases',
        'AllNoUpdateCases' => 'openAllNoUpdateCases',
        'AllOpenCases' => 'resetFilterSelection',

    ];

    public function mount()
    {
        $this->authorize('viewAny', tickets::class);
        $this->displayCaseStatus = $this->dashboardFilter ?? CaseState::Open->value;
    }

    protected function ticketService(): CaseTicketServices
    {
        return app(CaseTicketServices::class);
    }

    public function resetAllModal()
    {
        $this->reset([
            'displayProfileModal',
            'displayCreateModal',
            'displayUpdateModal',
            'displaySummaryModal',
            'displayDescription',
            'displaySlaStatus',
            'displayCustomerModal',
        ]);
    }

    public function resetAllFiltersAndSorting()
    {
        $this->reset([
            'filter',
            'sortField',
            'sortDirection',
            'displayCaseStatus',
            'displayDescription',
            'descriptionDetails',
        ]);
        $this->resetAllModal();
    }

    public function closeCreateModal()
    {
        $this->resetAllModal();
    }

    public function closeUpdateModal()
    {
        $this->resetAllModal();
    }

    public function closeProfileModal()
    {
        $this->resetAllModal();
    }

    public function closeSummaryModal()
    {
        $this->resetAllModal();
    }

    public function closeDescriptionModal()
    {
        $this->resetAllModal();
    }

    public function openAllResolvedCases()
    {
        $this->displayCaseStatus = CaseState::Resolved->value;
    }

    public function openAllNearBreachedCases()
    {
        $this->displayCaseStatus = 'nearBreached';
    }

    public function openAllBreachedCases()
    {
        $this->displayCaseStatus = 'breached';
    }

    public function openAllUnassignedCases()
    {
        $this->displayCaseStatus = 'unassigned';
    }

    public function openAllCompletedCases()
    {
        $this->displayCaseStatus = CaseState::Completed->value;
    }

    public function openAllClosedCases()
    {
        $this->displayCaseStatus = CaseState::Closed->value;
    }

    public function openAllPausedCases()
    {
        $this->displayCaseStatus = CaseState::Paused->value;
    }

    public function openAllNoUpdateCases()
    {
        $this->displayCaseStatus = 'noUpdate';
    }

    public function resetFilterSelection()
    {
        $this->displayCaseStatus = CaseState::Open->value;
    }

    public function buildUI()
    {
        return [
            'title' => 'Cases',
        ];
    }

    public function sortByField(?string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleViewMode(): void
    {
        $this->viewMode = $this->viewMode === 'table' ? 'workflow' : 'table';
    }

    private function workflowStage(tickets $case): int
    {
        $state = $case->state;

        if (
            $case->closed_datetime !== null
            || \in_array($state, [CaseState::Completed, CaseState::Closed, CaseState::Cancelled], true)
        ) {
            return 4;
        }

        if ($case->resolved_datetime !== null || $state === CaseState::Resolved) {
            return 3;
        }

        if ($state !== null && $state !== CaseState::Open) {
            return 2;
        }

        if ($case->assigned_to !== null) {
            return 1;
        }

        return 0;
    }

    public function openDescription(int|string $caseId)
    {
        $this->resetAllModal();
        $caseId = (int) $caseId;
        $column = 'id';
        $ticket = $this->tickets->firstWhere($column, $caseId);
        $siteName = $ticket?->customer?->name;
        $description = $ticket?->description;
        $this->descriptionDetails = [
            'name' => $siteName,
            'description' => $description,
        ];
        $this->displayDescription = true;
    }

    public function openSlaStatus(int|string $caseId)
    {
        $this->resetAllModal();
        $caseId = (int) $caseId;
        $column = 'id';
        $this->ticket = $this->tickets->firstWhere($column, $caseId);
        $this->displaySlaStatus = true;
    }

    public function openCreateModal()
    {
        $this->resetAllModal();
        $this->displayCreateModal = true;
    }

    public function openUpdateModal(int $caseId)
    {
        $this->resetAllModal();
        $this->caseId = $caseId;
        $this->displayUpdateModal = true;
    }

    public function openSummaryModal(int $caseId)
    {
        $this->resetAllModal();
        $this->caseId = $caseId;
        $this->displaySummaryModal = true;
    }

    public function openCustomerSummaryModal(int $customerId)
    {
        $this->resetAllModal();
        $this->customer = Customer::find($customerId);
        $this->displayCustomerModal = true;
    }

    public function openProfileModal(int $caseId)
    {
        $this->resetAllModal();
        $caseId = (int) $caseId;
        $column = 'id';
        $this->ticket = $this->tickets->firstWhere($column, $caseId);
        if (! $this->ticket) {
            $this->ticket = $this->ticketService()->getCaseById($caseId);
        }
        $this->displayProfileModal = true;
    }

    public function changeDisplayTo(?string $status)
    {
        $this->displayCaseStatus = $status;
    }

    public function openTab(CaseState $status)
    {
        $this->resetAllFiltersAndSorting();
        $this->displayCaseStatus = $status->value;
    }

    public function loadCases()
    {
        $service = $this->ticketService();
        $filterString = $this->filter ?? null;
        $query = null;

        if ($this->sortField) {
            $service->sortByField($this->sortField, $this->sortDirection);
        }

        if ($this->customerId) {
            $service->setCustomer($this->customerId);
        }

        if ($this->displayCaseStatus == 'breached') {
            $query = $service->getAllBreachedCases($filterString);
            $this->listTitle = 'Breached';
        }

        if ($this->displayCaseStatus == 'nearBreached') {
            $query = $service->getAllNearBreachCases($filterString);
            $this->listTitle = 'Near Breach';
        }

        if ($this->displayCaseStatus == 'unassigned') {
            $query = $service->getAllUnassignedCases($filterString);
            $this->listTitle = 'Unassigned';
        }

        if ($this->displayCaseStatus == 'noUpdate') {
            $query = $service->getAllNoUpdateCases($filterString);
            $this->listTitle = 'Awaiting Action';
        }

        if ($this->displayCaseStatus == CaseState::Open->value) {
            $query = $service->getAllOpenCases($filterString);
            $this->listTitle = $this->displayCaseStatus;
        }

        if ($this->displayCaseStatus === CaseState::In_Progress->value) {
            $query = $service->getOutstandingCases($filterString);
            $this->listTitle = $this->displayCaseStatus;
        }

        if ($this->displayCaseStatus === CaseState::Resolved->value) {
            $query = $service->getAllOpenResolvedCases($filterString);
            $this->listTitle = $this->displayCaseStatus;
        }

        if ($this->displayCaseStatus === CaseState::Completed->value) {
            $query = $service->getAllClosedResolvedCases($filterString);
            $this->listTitle = $this->displayCaseStatus;
        }

        if ($this->displayCaseStatus === CaseState::Closed->value) {
            $query = $service->getAllClosedCases($filterString);
            $this->listTitle = $this->displayCaseStatus;
        }

        if ($this->displayCaseStatus === CaseState::Paused->value) {
            $query = $service->getAllPausedCases($filterString);
            $this->listTitle = $this->displayCaseStatus;
        }

        if ($this->displayCaseStatus === CaseState::Site_Visit->value) {
            $query = $service->getAllVisitCases($filterString);
            $this->listTitle = $this->displayCaseStatus;
        }

        if (isset($query)) {
            $query->with(['customer', 'assignedToUser', 'slaApplication']);
            $query->withExists([
                'taskLogs as has_unanswered_external_comment' => function (Builder $query): void {
                    $query->whereNotNull('external_user')
                        ->where(function (Builder $query): void {
                            $query->whereNull('response_to_internal_comment')
                                ->orWhere('response_to_internal_comment', false);
                        })
                        ->whereDoesntHave('repliesToInternalComment');
                },
            ]);
            $this->tickets = $query->orderByDesc('created_at')->get();

            return $query;
        }

        return null;

    }

    public function render()
    {
        $cases = null;
        $ui = $this->buildUI();
        $cases = $this->loadCases()?->paginate(10);
        $workflowStageByCaseId = [];

        if ($cases) {
            foreach ($cases as $case) {
                $workflowStageByCaseId[$case->id] = $this->workflowStage($case);
            }
        }

        $hideStatus = $this->customerId || $this->dashboardFilter;

        return view('casemanagement::livewire.case.view-cases', [
            'ui_params' => $ui,
            'cases' => $cases,
            'hideStatus' => $hideStatus,
            'workflowStageByCaseId' => $workflowStageByCaseId,
        ]);

    }
}
