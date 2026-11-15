<?php

namespace Simplydigital\AIManagement\Livewire;

use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Simplydigital\AIManagement\Models\AIActionProposal;
use Simplydigital\AIManagement\Services\AIApprovalService;
use Simplydigital\AIManagement\Services\CaseWorkStartProposalService;
use Simplydigital\CaseManagement\Models\tickets;

class CaseWorkStartApprovals extends Component
{
    #[Locked]
    public int $caseId;

    public ?string $notice = null;
    public string $rejectionReason = '';

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

    public function prepare(CaseWorkStartProposalService $service): void
    {
        $case = $this->case();
        Gate::authorize('ai-work-start.propose', $case);
        try {
            $service->propose($case);
            $this->notice = 'Start Work proposal prepared for human approval.';
        } catch (\DomainException $exception) {
            $this->notice = $exception->getMessage();
        }
    }

    public function approve(string $proposalId, AIApprovalService $service): void
    {
        $case = $this->case();
        Gate::authorize('ai-work-start.approve', $case);
        $proposal = AIActionProposal::query()->where('case_id', $case->id)
            ->where('action_type', 'start_case_work')->findOrFail($proposalId);
        try {
            $service->approve($proposal->id, auth()->user());
            $this->notice = config('ai-management.work_start.execution_enabled', false)
                ? 'Approved. Start Work execution queued.'
                : 'Approved and recorded. Start Work execution is disabled.';
        } catch (\DomainException $exception) {
            $this->notice = $exception->getMessage();
        }
    }

    public function reject(string $proposalId, AIApprovalService $service): void
    {
        $case = $this->case();
        Gate::authorize('ai-work-start.approve', $case);
        $proposal = AIActionProposal::query()->where('case_id', $case->id)
            ->where('action_type', 'start_case_work')->findOrFail($proposalId);
        $this->validate(['rejectionReason' => ['nullable', 'string', 'max:1000']]);
        try {
            $service->reject($proposal->id, auth()->user(), $this->rejectionReason ?: null);
            $this->rejectionReason = '';
            $this->notice = 'Start Work proposal rejected. Case state unchanged.';
        } catch (\DomainException $exception) {
            $this->notice = $exception->getMessage();
        }
    }

    public function render()
    {
        $case = $this->case();
        return view('ai-management::livewire.case-work-start-approvals', [
            'case' => $case,
            'proposals' => AIActionProposal::query()
                ->where('case_id', $case->id)
                ->where('action_type', 'start_case_work')
                ->with(['approvals.reviewer', 'execution'])
                ->latest()->limit(10)->get(),
        ]);
    }
}
