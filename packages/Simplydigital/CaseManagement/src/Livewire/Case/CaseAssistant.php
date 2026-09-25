<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use Livewire\Component;
use Simplydigital\AIManagement\AI\Services\CaseAssistant as AIService;
use Simplydigital\CaseManagement\AI\CaseContextBuilder;
use Simplydigital\CaseManagement\Models\tickets;

class CaseAssistant extends Component
{
    public tickets $case;

    public function analyze(
        AIService $assistant
    ): void {
        $this->authorize(
            'case-assistant.analyze',
            $this->case
        );

        // Build CaseContext here.
        // Dispatch AIManagement job.
        $contextBuilder = app(CaseContextBuilder::class);
        $context = $contextBuilder->build($this->case);
        $this->dispatch('case-assistant-analysis-requested');        
    }

    public function render()
    {
        return view(
            'casemanagement::livewire.case.case-assistant'
        );
    }
}
