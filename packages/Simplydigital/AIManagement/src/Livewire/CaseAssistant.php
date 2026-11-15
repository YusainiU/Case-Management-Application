<?php

namespace Simplydigital\AIManagement\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Simplydigital\AIManagement\Models\AIActionProposal;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Enums\CaseState;
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

    /**
     * Read-only attention items from current operational records.
     *
     * Historical progress logs are intentionally not used for this panel:
     * an old failed/queued event is not evidence of a current problem.
     */
    private function attentionItems(): array
    {
        $caseId = $this->case->getKey();
        $items = [];

        $proposals = AIActionProposal::query()
            ->where('case_id', $caseId)
            ->where(function ($query): void {
                $query->where(function ($pending): void {
                    $pending->where('status', 'pending')
                        ->where('expires_at', '>', now());
                })->orWhereHas('execution', function ($execution): void {
                    $execution->where('status', 'failed');
                });
            })
            ->with('execution')
            ->latest('updated_at')
            ->limit(30)
            ->get();

        foreach ($proposals as $proposal) {
            $workflow = match ($proposal->action_type) {
                'update_case_category' => 'category',
                'assign_case_engineer' => 'engineer',
                'start_case_work' => 'start-work',
                default => null,
            };
            if ($workflow === null) {
                continue;
            }

            $action = match ($workflow) {
                'category' => 'Category change',
                'engineer' => 'Engineer assignment',
                default => 'Start Work',
            };

            if ($proposal->execution?->status === 'failed') {
                $items[] = [
                    'key' => 'execution:'.$proposal->id,
                    'title' => $action.' execution failed',
                    'detail' => 'Review the failed execution and case state before creating another request.',
                    'workflow' => $workflow,
                    'severity' => 'error',
                    'at' => $proposal->execution->updated_at ?? $proposal->updated_at,
                ];
            } elseif ($proposal->status === 'pending' && $proposal->expires_at?->isFuture()) {
                $items[] = [
                    'key' => 'pending:'.$proposal->id,
                    'title' => $action.' awaiting review',
                    'detail' => 'Approval request expires '.$proposal->expires_at
                        ->timezone(config('app.timezone', 'UTC'))->format('d M Y, H:i').' '.config('app.timezone', 'UTC'),
                    'workflow' => $workflow,
                    'severity' => 'pending',
                    'at' => $proposal->created_at,
                ];
            }
        }

        $latestAnalysis = AIAnalysis::query()
            ->where('case_id', $caseId)
            ->where('type', 'case_assistant')
            ->latest('id')
            ->first();

        if ($latestAnalysis?->status === 'failed') {
            $items[] = [
                'key' => 'analysis:'.$latestAnalysis->id,
                'title' => 'Latest Case Assistant analysis failed',
                'detail' => 'Review the analysis status and re-analyze when appropriate.',
                'workflow' => 'analysis',
                'severity' => 'error',
                'at' => $latestAnalysis->updated_at,
            ];
        }

        $notification = DB::table('ai_resolution_notifications')
            ->where('case_id', $caseId)
            ->first();

        if ($notification && in_array($notification->status, ['failed', 'skipped'], true)) {
            $items[] = [
                'key' => 'notification:'.$notification->id,
                'title' => $notification->status === 'failed'
                    ? 'Customer resolution email failed' : 'Customer resolution email skipped',
                'detail' => 'Inspect notification status and recipient details; do not blindly resend.',
                'workflow' => 'resolution',
                'severity' => 'error',
                'at' => Carbon::parse($notification->updated_at),
            ];
        }

        usort($items, fn (array $a, array $b): int =>
            ($b['at']?->getTimestamp() ?? 0) <=> ($a['at']?->getTimestamp() ?? 0)
        );

        return array_slice($items, 0, 12);
    }

    /**
     * Case workflow overview, based on persisted facts only.
     * An existing category is not evidence of AI approval, and a handoff
     * does not imply the case has actually been resolved.
     */
    private function workflowOverview(tickets $case): array
    {
        $caseId = $case->getKey();
        $analysis = AIAnalysis::query()
            ->where('case_id', $caseId)
            ->where('type', 'case_assistant')
            ->latest('id')
            ->first();

        $review = TaskLogs::query()
            ->where('ticket_id', $caseId)
            ->where('source', 'ai_assisted')
            ->latest('id')->first();

        $handoff = TaskLogs::query()
            ->where('ticket_id', $caseId)
            ->where('source', 'ai_resolution_review')
            ->latest('id')->first();

        $proposals = AIActionProposal::query()
            ->where('case_id', $caseId)
            ->whereIn('action_type', [
                'update_case_category', 'assign_case_engineer', 'start_case_work',
            ])
            ->with('execution')
            ->latest('created_at')
            ->get()
            ->groupBy('action_type')
            ->map(fn ($records) => $records->first());

        $pending = static fn ($proposal): bool =>
            $proposal !== null
            && $proposal->status === 'pending'
            && $proposal->expires_at?->isFuture();

        $started = $case->state instanceof CaseState
            && ! in_array($case->state, [CaseState::Open, CaseState::Cancelled], true);

        $resolved = $case->state === CaseState::Resolved
            || $case->state === CaseState::Closed
            || $case->state === CaseState::Completed
            || $case->resolved_datetime !== null;

        return [
            [
                'workflow' => 'analysis',
                'title' => 'Case analysis',
                'status' => $analysis?->status === 'completed' ? 'complete'
                    : (in_array($analysis?->status, ['pending', 'processing'], true) ? 'pending'
                    : ($analysis?->status === 'failed' ? 'attention' : 'not_started')),
                'detail' => match ($analysis?->status) {
                    'completed' => 'Latest analysis completed',
                    'pending', 'processing' => 'Analysis is '.$analysis->status,
                    'failed' => 'Latest analysis failed',
                    default => 'No analysis recorded',
                },
                'at' => $analysis?->updated_at,
            ],
            [
                'workflow' => 'category',
                'title' => 'Category review',
                'status' => $pending($proposals->get('update_case_category')) ? 'pending'
                    : ($proposals->get('update_case_category')?->execution?->status === 'completed' ? 'complete' : 'not_started'),
                'detail' => $pending($proposals->get('update_case_category'))
                    ? 'Awaiting category approval'
                    : ($proposals->get('update_case_category')?->execution?->status === 'completed'
                        ? 'Approved category change executed'
                        : 'No completed AI category change recorded'),
                'at' => $proposals->get('update_case_category')?->updated_at,
            ],
            [
                'workflow' => 'engineer',
                'title' => 'Engineer assignment',
                'status' => $case->assigned_to ? 'complete'
                    : ($pending($proposals->get('assign_case_engineer')) ? 'pending' : 'not_started'),
                'detail' => $case->assigned_to
                    ? 'Currently assigned: '.($case->assignedToUser?->name ?? 'User #'.$case->assigned_to)
                    : ($pending($proposals->get('assign_case_engineer'))
                        ? 'Awaiting assignment approval' : 'No engineer assigned'),
                'at' => $proposals->get('assign_case_engineer')?->updated_at,
            ],
            [
                'workflow' => 'start-work',
                'title' => 'Work started',
                'status' => $started ? 'complete'
                    : ($pending($proposals->get('start_case_work')) ? 'pending' : 'not_started'),
                'detail' => $started ? 'Current case state: '.$case->state->value.' (not necessarily AI-started)'
                    : ($pending($proposals->get('start_case_work'))
                        ? 'Start Work approval pending' : 'Case remains Open'),
                'at' => $proposals->get('start_case_work')?->updated_at,
            ],
            [
                'workflow' => 'troubleshoot',
                'title' => 'Troubleshooting review',
                'status' => $review ? 'complete' : 'not_started',
                'detail' => $review ? 'Reviewed internal note saved' : 'No reviewed troubleshooting note saved',
                'at' => $review?->created_at,
            ],
            [
                'workflow' => 'resolution',
                'title' => 'Resolution',
                'status' => $resolved ? 'complete' : ($handoff ? 'pending' : 'not_started'),
                'detail' => $resolved ? 'Case is resolved or completed'
                    : ($handoff ? 'Handoff saved; case not resolved' : 'No resolution handoff recorded'),
                'at' => $case->resolved_datetime ? \Illuminate\Support\Carbon::createFromTimestamp((int) $case->resolved_datetime)
                    : $handoff?->created_at,
            ],
        ];
    }

    public function render()
    {
        Gate::authorize('view', $this->case);
        $currentCase = tickets::query()->with('assignedToUser')->findOrFail($this->case->getKey());

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
            'attentionItems' => $this->attentionItems(),
            'workflowOverview' => $this->workflowOverview($currentCase),
        ]);
    }
}