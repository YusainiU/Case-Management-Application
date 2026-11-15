<?php

namespace Simplydigital\AIManagement\Livewire;

use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Simplydigital\AIManagement\Models\AIActionProposal;
use Simplydigital\AIManagement\Services\AIApprovalService;
use Simplydigital\AIManagement\Services\EngineerAssignmentProposalService;
use Simplydigital\AIManagement\Services\EngineerRecommendationService;
use Simplydigital\CaseManagement\Models\tickets;

class EngineerAssignmentApprovals extends Component
{
    #[Locked]
    public int $caseId;

    public ?int $selectedEngineerId = null;

    public string $rejectionReason = '';

    public ?string $notice = null;

    public function mount(tickets $case): void
    {
        $this->caseId = $case->getKey();
        $this->case();
    }

    private function case(): tickets
    {
        $case = tickets::query()->findOrFail($this->caseId);
        Gate::authorize('view', $case);

        return $case;
    }

    public function prepare(EngineerAssignmentProposalService $service): void
    {
        $case = $this->case();
        Gate::authorize('ai-engineer.propose', $case);
        $this->validate(['selectedEngineerId' => ['required', 'integer']]);

        try {
            $service->propose($case, $this->selectedEngineerId);
            $this->notice = 'Engineer assignment submitted for approval. No assignment has changed.';
        } catch (\DomainException $exception) {
            $this->notice = $exception->getMessage();
        }
    }

    public function approve(string $id, AIApprovalService $service): void
    {
        $case = $this->case();
        Gate::authorize('ai-engineer.approve', $case);
        $proposal = AIActionProposal::query()
            ->where('case_id', $case->id)
            ->where('action_type', 'assign_case_engineer')
            ->findOrFail($id);

        try {
            $service->approve($proposal->id, auth()->user());
            $this->notice = config('ai-management.engineer_assignment.execution_enabled', false)
                ? 'Approved. Execution is queued.'
                : 'Approved and recorded. Engineer execution is disabled.';
        } catch (\DomainException $exception) {
            $this->notice = $exception->getMessage();
        }
    }

    public function reject(string $id, AIApprovalService $service): void
    {
        $case = $this->case();
        Gate::authorize('ai-engineer.approve', $case);
        $proposal = AIActionProposal::query()
            ->where('case_id', $case->id)
            ->where('action_type', 'assign_case_engineer')
            ->findOrFail($id);

        $this->validate(['rejectionReason' => ['nullable', 'string', 'max:1000']]);
        try {
            $service->reject($proposal->id, auth()->user(), $this->rejectionReason ?: null);
            $this->rejectionReason = '';
            $this->notice = 'Proposal rejected; engineer assignment unchanged.';
        } catch (\DomainException $exception) {
            $this->notice = $exception->getMessage();
        }
    }

    public function render()
    {
        $case = $this->case();

        return view('ai-management::livewire.engineer-assignment-approvals', [
            'case' => $case,
            'recommendations' => app(EngineerRecommendationService::class)->recommend($case),
            'proposals' => AIActionProposal::query()
                ->where('case_id', $case->id)
                ->where('action_type', 'assign_case_engineer')
                ->with(['approvals.reviewer', 'execution'])
                ->latest()->limit(10)->get(),
        ]);
    }
}
