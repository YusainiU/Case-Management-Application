<?php

namespace Simplydigital\AIManagement\Livewire;

use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Simplydigital\AIManagement\Models\AIActionProposal;
use Simplydigital\AIManagement\Services\AIApprovalService;
use Simplydigital\CaseManagement\Models\tickets;

#[Layout('components.layouts.app')]
class ApprovalReviewQueue extends Component
{
    use WithPagination;

    #[Url]
    public string $status = 'pending';

    public string $rejectionReason = '';

    public ?string $notice = null;

    public function mount(): void
    {
        $this->authorizeQueueAccess();
    }

    private function authorizeQueueAccess(): void
    {
        Gate::authorize('ai.proposals.view');
        Gate::authorize('viewAny', tickets::class);
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function approve(string $proposalId, AIApprovalService $service): void
    {
        $this->review($proposalId, $service, false);
    }

    public function reject(string $proposalId, AIApprovalService $service): void
    {
        $this->validate([
            'rejectionReason' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->review($proposalId, $service, true);
    }

    private function review(string $proposalId, AIApprovalService $service, bool $reject): void
    {
        $this->authorizeQueueAccess();

        $proposal = AIActionProposal::query()->findOrFail($proposalId);
        $case = tickets::query()->findOrFail($proposal->case_id);
        Gate::authorize(
            match ($proposal->action_type) {
                'update_case_category' => 'ai-category.approve',
                'assign_case_engineer' => 'ai-engineer.approve',
                'start_case_work' => 'ai-work-start.approve',
                default => abort(404),
            },
            $case,
        );

        try {
            if ($reject) {
                $service->reject($proposal->id, auth()->user(), $this->rejectionReason ?: null);
                $this->rejectionReason = '';
                $this->notice = 'Proposal rejected; no action was executed.';
            } else {
                $service->approve($proposal->id, auth()->user());
                $canExecute = match ($proposal->action_type) {
                    'update_case_category' => config('ai-management.approvals.execution_enabled', false),
                    'assign_case_engineer' => config('ai-management.engineer_assignment.execution_enabled', false),
                    'start_case_work' => config('ai-management.work_start.execution_enabled', false),
                    default => false,
                };
                $this->notice = $canExecute
                    ? 'Proposal approved and execution queued.'
                    : 'Proposal approved and recorded. Execution is disabled.';
            }
        } catch (\DomainException $exception) {
            $this->notice = $exception->getMessage();
        }
    }

    public function render()
    {
        $this->authorizeQueueAccess();

        $statuses = ['all', 'pending', 'approved', 'rejected', 'completed', 'stale', 'failed'];
        // A manipulated Livewire filter must not be accepted as an arbitrary query.
        if (! in_array($this->status, $statuses, true)) {
            $this->status = 'pending';
        }

        $query = AIActionProposal::query()
            ->whereHas('case')
            ->with(['case', 'approvals.reviewer', 'execution'])
            ->latest();

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        $proposals = $query->paginate(15);
        $engineerIds = $proposals->getCollection()
            ->where('action_type', 'assign_case_engineer')
            ->pluck('payload')
            ->pluck('engineer_user_id')
            ->filter()->unique()->all();
        $engineerNames = \App\Models\User::query()->whereIn('id', $engineerIds)->pluck('name', 'id');

        return view('ai-management::livewire.approval-review-queue', [
            'engineerNames' => $engineerNames,
            'proposals' => $proposals,
            'statuses' => $statuses,
        ]);
    }
}
