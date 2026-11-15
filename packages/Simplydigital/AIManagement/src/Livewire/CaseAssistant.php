<?php

namespace Simplydigital\AIManagement\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Gate;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\AIManagement\Models\AIAnalysis;
use Simplydigital\AIManagement\Enums\AIStatuses;
  use Simplydigital\CaseManagement\AI\CaseAssistantService;

class CaseAssistant extends Component
{
    public object $case;

    public ?int $analysisId = null;

    public bool $analyzing = false;

    public int $timelineLimit = 20;

    public function loadMoreTimeline(): void
    {
        Gate::authorize('view', $this->case);
        $this->timelineLimit = min(100, $this->timelineLimit + 20);
    }

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

         $analysis = app(CaseAssistantService::class)
            ->requestAnalysis($this->case);

        $this->analysisId = $analysis->id;
        $this->analyzing = true;

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
        Gate::authorize('view', $this->case);

        // Each AI lifecycle stage already writes an internal case progress log.
        // Use that record as the single chronological source of truth, rather
        // than displaying proposal and analysis summary states as duplicates.
        $entries = TaskLogs::query()
            ->where('ticket_id', $this->case->getKey())
            ->whereIn('source', ['ai_agent', 'ai_assisted', 'ai_resolution_review'])
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($this->timelineLimit + 1)
            ->get();

        $hasMore = $entries->count() > $this->timelineLimit;

        return view('ai-management::livewire.case-assistant', [
            'analysis' => $this->analysis,
            'result' => $this->analysis?->result,
            'timelineEntries' => $entries->take($this->timelineLimit),
            'timelineHasMore' => $hasMore,
            'timelineTimezone' => config('app.timezone', 'UTC'),
        ]);
    }
}