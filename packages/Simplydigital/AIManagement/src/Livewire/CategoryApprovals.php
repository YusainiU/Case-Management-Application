<?php

namespace Simplydigital\AIManagement\Livewire;

use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Simplydigital\AIManagement\Models\AIActionProposal;
use Simplydigital\AIManagement\Models\AIAnalysis;
use Simplydigital\AIManagement\Services\AIApprovalService;
use Simplydigital\AIManagement\Services\CategoryProposalService;
use Simplydigital\CaseManagement\Models\tickets;

class CategoryApprovals extends Component
{
    #[Locked]
    public int $caseId;

    public string $rejectionReason = '';

    public ?string $notice = null;

    public function mount(tickets $case): void
    {
        $this->caseId = $case->getKey();
        Gate::authorize('view', $case);
    }

    private function currentCase(): tickets
    {
        $case = tickets::findOrFail($this->caseId);
        Gate::authorize('view', $case);

        return $case;
    }

    public function prepare(CategoryProposalService $service): void
    {
        $case = $this->currentCase();
        Gate::authorize('case-assistant.analyze', $case);

        $analysis = AIAnalysis::query()
            ->where('case_id', $case->id)
            ->where('type', 'case_assistant')
            ->where('status', 'completed')
            ->latest()
            ->first();

        if (! $analysis) {
            $this->notice = 'No completed case analysis is available.';
            return;
        }

        try {
            $proposal = $service->fromAnalysis($analysis);
            $this->notice = $proposal
                ? 'AI category proposal prepared for approval.'
                : 'No valid category change was suggested.';
        } catch (\DomainException $exception) {
            $this->notice = $exception->getMessage();
        }
    }

    public function approve(string $proposalId, AIApprovalService $service): void
    {
        $case = $this->currentCase();
        Gate::authorize('ai-category.approve', $case);

        $proposal = AIActionProposal::query()
            ->where('case_id', $case->id)
            ->findOrFail($proposalId);

        try {
            $service->approve($proposal->id, auth()->user());
            $this->notice = 'Approved. Execution is queued.';
        } catch (\DomainException $exception) {
            $this->notice = $exception->getMessage();
        }
    }

    public function reject(string $proposalId, AIApprovalService $service): void
    {
        $case = $this->currentCase();
        Gate::authorize('ai-category.approve', $case);

        $proposal = AIActionProposal::query()
            ->where('case_id', $case->id)
            ->findOrFail($proposalId);

        $this->validate([
            'rejectionReason' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $service->reject($proposal->id, auth()->user(), $this->rejectionReason ?: null);
            $this->rejectionReason = '';
            $this->notice = 'Proposal rejected; no change was made.';
        } catch (\DomainException $exception) {
            $this->notice = $exception->getMessage();
        }
    }

    public function render()
    {
        $case = $this->currentCase();

        return view('ai-management::livewire.category-approvals', [
            'case' => $case,
            'analysis' => AIAnalysis::query()
                ->where('case_id', $case->id)
                ->where('type', 'case_assistant')
                ->where('status', 'completed')
                ->latest()
                ->first(),
            'proposals' => AIActionProposal::query()
                ->where('case_id', $case->id)
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }
}
