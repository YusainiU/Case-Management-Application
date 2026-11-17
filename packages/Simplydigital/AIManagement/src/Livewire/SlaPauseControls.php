<?php

namespace Simplydigital\AIManagement\Livewire;

use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseTicketServices;

class SlaPauseControls extends Component
{
    #[Locked]
    public int $caseId;

    public bool $confirmPause = false;
    public ?string $notice = null;

    public function mount(tickets $case): void
    {
        $this->caseId = (int) $case->getKey();
        $this->case();
    }

    private function case(): tickets
    {
        $case = tickets::query()->findOrFail($this->caseId);
        Gate::authorize('view', $case);

        return $case;
    }

    /**
     * Match the existing Manage SLA screen's working-day/hour and breach
     * restrictions, in addition to the case-update permission.
     */
    private function eligibility(tickets $case): array
    {
        $sla = $case->slaTasks;
        if (! $sla) {
            return ['allowed' => false, 'reason' => 'The case has no SLA task. Use the normal SLA management screen to create one.'];
        }
        if (! $case->slaApplication?->serviceLevelAgreement) {
            return ['allowed' => false, 'reason' => 'The case has no valid SLA policy. Review SLA configuration in the normal case management screen.'];
        }
        if ($case->closed_datetime !== null || $case->resolved_datetime !== null
            || ! in_array($case->state, CaseState::openStates(), true)) {
            return ['allowed' => false, 'reason' => 'Resolved, closed or inactive cases cannot change their SLA pause.'];
        }
        if ($sla->breached) {
            return ['allowed' => false, 'reason' => 'This SLA has already breached. Review the normal SLA workflow.'];
        }

        $service = app(CaseTicketServices::class);
        if (! $service->isTodayBusinessWorkingDay($case)
            || ! $service->isNowInsideServiceHours($case)) {
            return ['allowed' => false, 'reason' => 'SLA pause controls are available during the configured SLA working days and hours.'];
        }

        return ['allowed' => true, 'reason' => null];
    }

    public function pause(): void
    {
        $case = $this->case();
        Gate::authorize('update', $case);
        $this->validate(['confirmPause' => ['accepted']]);

        $eligible = $this->eligibility($case);
        if (! $eligible['allowed'] || $case->state === CaseState::Paused || $case->slaTasks?->paused) {
            $this->notice = $eligible['reason'] ?? 'The SLA is already paused or the case is not eligible.';
            return;
        }

        $result = app(CaseTicketServices::class)->pauseSla($case);
        $this->confirmPause = false;
        $this->notice = $result
            ? 'SLA paused using the normal case management process. Remaining response/fix time is recorded.'
            : 'The SLA could not be paused. Refresh its status and review the standard SLA screen.';
        $this->dispatch('reloadCaseStatus');
    }

    public function resume(): void
    {
        $case = $this->case();
        Gate::authorize('update', $case);

        $eligible = $this->eligibility($case);
        if (! $eligible['allowed'] || $case->state !== CaseState::Paused
            || ! $case->slaTasks?->paused) {
            $this->notice = $eligible['reason'] ?? 'There is no active SLA pause to resume.';
            return;
        }

        $result = app(CaseTicketServices::class)->resumeSla($case);
        $this->notice = $result
            ? 'SLA resumed using the normal case management process. Response and fix deadlines were recalculated.'
            : 'The SLA could not be resumed. Review the standard SLA screen.';
        $this->dispatch('reloadCaseStatus');
    }

    public function cancelPause(): void
    {
        $case = $this->case();
        Gate::authorize('update', $case);

        $eligible = $this->eligibility($case);
        if (! $eligible['allowed'] || $case->state !== CaseState::Paused
            || ! $case->slaTasks?->paused) {
            $this->notice = $eligible['reason'] ?? 'There is no active SLA pause to cancel.';
            return;
        }

        $result = app(CaseTicketServices::class)->cancelPauseSla($case);
        $this->notice = $result
            ? 'Pause cancelled using the normal case management process. The original SLA deadlines were kept.'
            : 'The SLA pause could not be cancelled. Review the standard SLA screen.';
        $this->dispatch('reloadCaseStatus');
    }

    public function render()
    {
        $case = $this->case()->load('slaTasks.activePause');
        return view('ai-management::livewire.sla-pause-controls', [
            'case' => $case,
            'sla' => $case->slaTasks,
            'eligibility' => $this->eligibility($case),
        ]);
    }
}
