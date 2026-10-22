<?php

namespace Simplydigital\CaseManagement\Livewire\Portal;

use Illuminate\View\View;
use Livewire\Component;
use Simplydigital\CaseManagement\Livewire\Portal\Concerns\InteractsWithPortalCases;

class ViewCase extends Component
{
    use InteractsWithPortalCases;

    public int $caseId;

    public function mount(int $case): void
    {
        $this->caseId = $case;
        $this->findPortalCase($this->caseId);
    }

    public function render(): View
    {
        return view('casemanagement::livewire.portal.view-case', [
            'case' => $this->findPortalCase($this->caseId),
        ])->layout('casemanagement::layouts.portal');
    }
}
