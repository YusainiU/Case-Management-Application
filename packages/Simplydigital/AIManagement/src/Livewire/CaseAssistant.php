<?php

namespace Simplydigital\AIManagement\Livewire;

use Livewire\Component;
use Simplydigital\AIManagement\AI\DTO\CaseAssistantResult;
use Simplydigital\CaseManagement\AI\CaseContextBuilder;
use Simplydigital\AIManagement\Jobs\GenerateCaseAssistantAnalysis;
use Simplydigital\AIManagement\Models\AIAnalysis;
use Simplydigital\AIManagement\Enums\AIStatuses;
use Simplydigital\CaseManagement\AI\CaseAssistantService;

class CaseAssistant extends Component
{
    public object $case;

    public ?int $analysisId = null;

    public ?CaseAssistantResult $result = null;

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

        $contextBuilder = app(
            CaseContextBuilder::class
        );

        $context = $contextBuilder->build(
            $this->case
        );

        $analysis = app(
            CaseAssistantService::class
        )->requestAnalysis(
            $this->case
        );        

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
    }

    protected function loadLatestAnalysis(): void
    {
        $analysis = AIAnalysis::query()
            ->where('case_id', $this->case->getKey())
            ->where('type', 'case_assistant')
            ->latest()
            ->first();

        if (!$analysis) {
            return;
        }

        $this->analysisId = $analysis->id;

        if ($analysis->status === 'completed') {
            $this->result = CaseAssistantResult::fromArray(
                $analysis->result ?? []
            );

            $this->analyzing = false;
        }

        if ($analysis->status === AIStatuses::Pending->value) {
            $this->analyzing = true;
        }

        if ($analysis->status === AIStatuses::Processing->value) {
            $this->analyzing = true;
        }

        if ($analysis->status === AIStatuses::Failed->value) {
            $this->analyzing = false;
        }
    }

    public function getAnalysisProperty(): ?AIAnalysis
    {
        if (!$this->analysisId) {
            return null;
        }

        return AIAnalysis::find(
            $this->analysisId
        );
    }

    public function render()
    {
        return view(
            'ai-management::livewire.case-assistant'
        );
    }
}
