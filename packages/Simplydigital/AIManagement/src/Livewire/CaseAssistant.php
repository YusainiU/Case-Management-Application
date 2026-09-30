<?php

namespace Simplydigital\AIManagement\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Simplydigital\AIManagement\Models\AIAnalysis;
use Simplydigital\AIManagement\Enums\AIStatuses;
use Simplydigital\CaseManagement\AI\CaseContextBuilder;
use Simplydigital\AIManagement\Jobs\GenerateCaseAssistantAnalysis;
use Simplydigital\CaseManagement\AI\CaseAssistantService;

class CaseAssistant extends Component
{
    public object $case;

    public ?int $analysisId = null;

    public bool $analyzing = false;

    public function mount(object $case): void
    {
        $this->case = $case;

        $this->loadLatestAnalysis();
    }

    public function analyze(): void
    {
        $this->authorize(
            'case-assistant.analyze',
            $this->case
        );

        $context = app(CaseContextBuilder::class)
            ->build($this->case);

        $analysis = app(CaseAssistantService::class)
            ->requestAnalysis($this->case);

        $this->analysisId = $analysis->id;
        $this->analyzing = true;

        GenerateCaseAssistantAnalysis::dispatch(
            analysisId: $analysis->id,
            context: $context,
        );

        $this->dispatch(
            'case-assistant-analysis-started'
        );
    }

    public function refreshAnalysis(): void
    {
        $this->loadLatestAnalysis();

        // Clear the computed property cache so the Blade
        // gets the latest AIAnalysis record.
        unset($this->analysis);
    }

    protected function loadLatestAnalysis(): void
    {
        $analysis = AIAnalysis::query()
            ->where('case_id', $this->case->getKey())
            ->where('type', 'case_assistant')
            ->latest()
            ->first();

        if (! $analysis) {
            return;
        }

        $this->analysisId = $analysis->id;

        $this->analyzing = \in_array(
            $analysis->status,
            [
                AIStatuses::Pending->value,
                AIStatuses::Processing->value,
            ],
            true
        );
        
    }

    #[Computed]
    public function analysis(): ?AIAnalysis
    {
        if (! $this->analysisId) {
            return null;
        }

        return AIAnalysis::find($this->analysisId);
    }

    public function render()
    {   
        return view(
            'ai-management::livewire.case-assistant',[
                'analysis' => $this->analysis,
                'result' => $this->analysis?->result,
            ]);
    }
}