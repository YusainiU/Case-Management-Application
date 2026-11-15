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

    public string $timelineFilter = 'all';

    private const TIMELINE_FILTERS = [
        'all', 'analysis', 'approvals', 'reviews', 'notifications',
    ];

    public function setTimelineFilter(string $filter): void
    {
        Gate::authorize('view', $this->case);

        if (! in_array($filter, self::TIMELINE_FILTERS, true)) {
            return;
        }

        $this->timelineFilter = $filter;
        $this->timelineLimit = 20;
    }

    public function openWorkflow(string $workflow): void
    {
        Gate::authorize('view', $this->case);

        if (in_array($workflow, self::WORKFLOWS, true)
            && ! in_array($workflow, $this->expandedWorkflows, true)) {
            $this->expandedWorkflows[] = $workflow;
        }
    }

    /** @var array<int, string> */
    public array $expandedWorkflows = [];

    private const WORKFLOWS = [
        'analysis', 'category', 'engineer', 'start-work', 'troubleshoot', 'resolution',
    ];

    public function toggleWorkflow(string $workflow): void
    {
        Gate::authorize('view', $this->case);

        if (! in_array($workflow, self::WORKFLOWS, true)) {
            return;
        }

        if (in_array($workflow, $this->expandedWorkflows, true)) {
            $this->expandedWorkflows = array_values(array_diff($this->expandedWorkflows, [$workflow]));
            return;
        }

        $this->expandedWorkflows[] = $workflow;
    }

    public function loadMoreTimeline(): void
    {
        Gate::authorize('view', $this->case);
        $this->timelineLimit = min(100, $this->timelineLimit + 20);
    }

    public function mount(object $case): void
    {
        $this->case = $case;

        $this->loadLatestAnalysis();

        // These are Livewire state rather than native <details> state:
        // polling must not collapse a panel while someone is using it.
        if ($this->analyzing) {
            $this->expandedWorkflows = ['analysis'];
        }
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

        if (! in_array('analysis', $this->expandedWorkflows, true)) {
            $this->expandedWorkflows[] = 'analysis';
        }

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
        $query = TaskLogs::query()
            ->where('ticket_id', $this->case->getKey())
            ->whereIn('source', ['ai_agent', 'ai_assisted', 'ai_resolution_review']);

        // Filter on recorded event keys and sources, not AI-written descriptions.
        // That keeps the chronology accurate and avoids mixing other case logs.
        match ($this->timelineFilter) {
            'analysis' => $query->where('source', 'ai_agent')
                ->where('short_description', 'like', 'AI:analysis:%'),
            'approvals' => $query->where('source', 'ai_agent')
                ->where('short_description', 'like', 'AI:proposal:%'),
            'reviews' => $query->whereIn('source', ['ai_assisted', 'ai_resolution_review']),
            'notifications' => $query->where('source', 'ai_agent')
                ->where('short_description', 'like', 'AI:resolution-notification:%'),
            default => null,
        };

        $entries = $query->with('user:id,name')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(min(100, $this->timelineLimit) + 1)
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