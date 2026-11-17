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

    // These are Livewire-owned presentation preferences. Polling and child
    // workflow actions must never reset them.
    public bool $showOverview = true;

    public bool $showTimeline = true;

    public function toggleOverview(): void
    {
        Gate::authorize('view', $this->case);
        $this->showOverview = ! $this->showOverview;
    }

    public function toggleTimeline(): void
    {
        Gate::authorize('view', $this->case);
        $this->showTimeline = ! $this->showTimeline;
    }

    public function setFocusView(bool $enabled): void
    {
        Gate::authorize('view', $this->case);

        $this->showOverview = ! $enabled;
        $this->showTimeline = ! $enabled;
    }

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
        'analysis', 'category', 'engineer', 'start-work', 'sla-pause', 'troubleshoot', 'resolution',
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
            // MySQL timestamps have second-level precision. Two proposals
            // created in the same second have no reliable "latest" order.
            // Prefer an active approval over historical proposals so an
            // outstanding request is never hidden by an ordering tie.
            ->map(fn ($records) => $records->first(
                fn ($proposal) => $proposal->status === 'pending'
                    && $proposal->expires_at?->isFuture()
            ) ?? $records->first());

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
                'workflow' => 'sla-pause',
                'title' => 'SLA pause / resume',
                'status' => $case->slaTasks?->paused ? 'pending'
                    : ($case->slaTasks ? 'ready' : 'not_started'),
                'detail' => $case->slaTasks?->paused
                    ? 'SLA is paused; an authorised user must resume or cancel the pause'
                    : ($case->slaTasks ? 'SLA active / not paused; manual controls available when eligible'
                        : 'No SLA task recorded'),
                'at' => $case->slaTasks?->activePause?->paused_at,
            ],
            [
                'workflow' => 'troubleshoot',
                'title' => 'Troubleshooting review',
                'status' => $review ? 'complete'
                    : ($analysis?->status === 'completed' ? 'ready' : 'not_started'),
                'detail' => $review ? 'Reviewed internal note saved'
                    : ($analysis?->status === 'completed'
                        ? 'Ready to review the completed analysis; no re-analysis required'
                        : 'No reviewed troubleshooting note saved'),
                'at' => $review?->created_at ?? ($analysis?->status === 'completed' ? $analysis->updated_at : null),
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

    /**
     * Advisory navigation based on live case facts, not a mandatory pipeline.
     * Human approval / update permissions remain in each workflow component.
     */
    private function suggestedNextStep(array $overview, array $attention): array
    {
        foreach ($attention as $item) {
            if ($item['severity'] === 'error') {
                return [
                    'title' => 'Review an outstanding issue',
                    'detail' => $item['title'].'. '.$item['detail'],
                    'workflow' => $item['workflow'],
                    'tone' => 'attention',
                ];
            }
        }

        $stages = collect($overview)->keyBy('workflow');
        $resolution = $stages->get('resolution');
        if ($resolution['status'] === 'complete') {
            return [
                'title' => 'Resolution recorded',
                'detail' => 'The case is already resolved or completed. Check the resolution and customer-notification status; no further AI action is automatically required.',
                'workflow' => 'resolution',
                'tone' => 'complete',
            ];
        }

        foreach ($attention as $item) {
            if ($item['severity'] === 'pending') {
                return [
                    'title' => 'Review an awaiting approval',
                    'detail' => $item['title'].'. A reviewer must decide whether to approve or reject.',
                    'workflow' => $item['workflow'],
                    'tone' => 'pending',
                ];
            }
        }

        $analysis = $stages->get('analysis');
        if ($analysis['status'] === 'pending') {
            return [
                'title' => 'Analysis in progress',
                'detail' => 'Wait for the current analysis to complete; no additional analysis request is needed.',
                'workflow' => 'analysis',
                'tone' => 'pending',
            ];
        }
        if ($analysis['status'] !== 'complete') {
            return [
                'title' => 'Review case analysis',
                'detail' => 'No completed current Case Assistant analysis is recorded. Run analysis only if it is needed for troubleshooting.',
                'workflow' => 'analysis',
                'tone' => 'neutral',
            ];
        }

        if ($stages->get('engineer')['status'] === 'not_started') {
            return [
                'title' => 'Review engineer assignment',
                'detail' => 'No engineer is currently assigned. Review the suggested assignment or use the normal manual assignment workflow.',
                'workflow' => 'engineer',
                'tone' => 'neutral',
            ];
        }

        if ($stages->get('start-work')['status'] === 'not_started') {
            return [
                'title' => 'Confirm work has started',
                'detail' => 'The case remains Open. Prepare Start Work approval only after the assigned engineer has actually begun.',
                'workflow' => 'start-work',
                'tone' => 'neutral',
            ];
        }

        // A paused SLA is an operational state, not evidence of inactivity.
        // Direct staff to review it before suggesting more case workflow changes.
        if ($stages->get('sla-pause')['status'] === 'pending') {
            return [
                'title' => 'SLA is paused',
                'detail' => 'Review the recorded pause and resume or cancel it when permitted by the SLA working-hours rules. No action is automatic.',
                'workflow' => 'sla-pause',
                'tone' => 'pending',
            ];
        }

        if ($stages->get('troubleshoot')['status'] !== 'complete') {
            return [
                'title' => 'Review troubleshooting suggestions',
                'detail' => 'Reuse the completed analysis to prepare a staff-reviewed internal note. No re-analysis is needed just because the case was updated.',
                'workflow' => 'troubleshoot',
                'tone' => 'neutral',
            ];
        }

        if ($resolution['status'] === 'pending') {
            return [
                'title' => 'Review the saved resolution handoff',
                'detail' => 'A handoff was saved, but the case is not resolved. Confirm the actual fix and verification before resolving.',
                'workflow' => 'resolution',
                'tone' => 'neutral',
            ];
        }

        return [
            'title' => 'Prepare resolution readiness',
            'detail' => 'A staff-reviewed troubleshooting note is recorded. Document the actual fix and verification; resolving the case remains optional and requires explicit approval.',
            'workflow' => 'resolution',
            'tone' => 'neutral',
        ];
    }

    public function render()
    {
        Gate::authorize('view', $this->case);
        $currentCase = tickets::query()->with(['assignedToUser', 'slaTasks.activePause'])->findOrFail($this->case->getKey());

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
        $attentionItems = $this->attentionItems();
        $overview = $this->workflowOverview($currentCase);

        return view('ai-management::livewire.case-assistant', [
            'analysis' => $this->analysis,
            'result' => $this->analysis?->result,
            'timelineEntries' => $entries->take($this->timelineLimit),
            'timelineHasMore' => $hasMore,
            'timelineTimezone' => config('app.timezone', 'UTC'),
            'attentionItems' => $attentionItems,
            'workflowOverview' => $overview,
            'suggestedNextStep' => $this->suggestedNextStep($overview, $attentionItems),
        ]);
    }
}