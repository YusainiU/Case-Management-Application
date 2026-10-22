<?php

namespace Simplydigital\CaseManagement\Livewire\Portal;

use Illuminate\View\View;
use Livewire\Component;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Livewire\Portal\Concerns\InteractsWithPortalCases;

class Dashboard extends Component
{
    use InteractsWithPortalCases;

    public function render(): View
    {
        $cases = $this->portalCasesQuery();
        $openStates = array_map(
            static fn (CaseState $state): string => $state->value,
            CaseState::openStates()
        );
        $closedStates = array_map(
            static fn (CaseState $state): string => $state->value,
            CaseState::closedStates()
        );

        return view('casemanagement::livewire.portal.dashboard', [
            'openCount' => (clone $cases)->whereIn('state', $openStates)->count(),
            'closedCount' => (clone $cases)->whereIn('state', $closedStates)->count(),
            'recentCases' => (clone $cases)
                ->latest('updated_at')
                ->latest('id')
                ->limit(5)
                ->get(),
        ])->layout('casemanagement::layouts.portal');
    }
}
