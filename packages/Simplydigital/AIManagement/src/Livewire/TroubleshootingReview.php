<?php

namespace Simplydigital\AIManagement\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Simplydigital\AIManagement\Models\AIAnalysis;
use Simplydigital\AIManagement\Services\TroubleshootingSolutionService;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\CaseManagement\Models\tickets;

class TroubleshootingReview extends Component
{
    #[Locked]
    public int $caseId;

    #[Locked]
    public ?int $sourceAnalysisId = null;

    #[Locked]
    public ?string $caseSnapshot = null;

    public string $draft = '';

    public bool $confirmed = false;

    public bool $confirmSolutionCredit = false;

    public ?string $notice = null;

    public function mount(tickets $case): void
    {
        $this->caseId = $case->getKey();
        $this->currentCase();
    }

    private function currentCase(): tickets
    {
        $case = tickets::query()->findOrFail($this->caseId);
        Gate::authorize('view', $case);

        return $case;
    }

    private function latestAnalysis(tickets $case): ?AIAnalysis
    {
        // Do not silently reuse an older completed result while a new
        // re-analysis is pending, processing, or has failed.
        return AIAnalysis::query()
            ->where('case_id', $case->id)
            ->where('type', 'case_assistant')
            ->latest('id')
            ->first();
    }

    private function isCompleted(?AIAnalysis $analysis): bool
    {
        return $analysis?->status === 'completed';
    }

    private function caseUpdatedSinceAnalysis(tickets $case, ?AIAnalysis $analysis): bool
    {
        // Workflow approvals, assignment and Start Work normally update the
        // case after analysis. Such updates are a review warning, not a reason
        // to require another AI request before a staff member can write notes.
        return $this->isCompleted($analysis)
            && $analysis->updated_at !== null
            && $case->updated_at !== null
            && $case->updated_at->greaterThan($analysis->updated_at);
    }

    private function snapshot(tickets $case): string
    {
        return hash('sha256', json_encode([
            $case->getKey(),
            $case->getRawOriginal('updated_at'),
            $case->getRawOriginal('short_description'),
            $case->getRawOriginal('description'),
            $case->getRawOriginal('state'),
            $case->assigned_to,
            $case->resolved_datetime,
            $case->closed_datetime,
        ], JSON_THROW_ON_ERROR));
    }

    public function recommendSolution(TroubleshootingSolutionService $service): void
    {
        $case = $this->currentCase();
        Gate::authorize('update', $case);
        Gate::authorize('case-assistant.analyze', $case);

        $this->validate([
            'confirmSolutionCredit' => ['accepted'],
        ], [
            'confirmSolutionCredit.accepted' => 'Confirm that this separate AI request may use provider credits.',
        ]);

        try {
            $service->request($case);
            $this->notice = 'Solution recommendation queued. Provider credits may be used; status updates automatically.';
            $this->confirmSolutionCredit = false;
        } catch (\DomainException $exception) {
            $this->notice = $exception->getMessage();
        }
    }

    public function prepare(): void
    {
        $case = $this->currentCase();
        Gate::authorize('update', $case);

        if ($case->closed_datetime !== null || $case->resolved_datetime !== null) {
            $this->notice = 'A resolved or closed case cannot receive a troubleshooting draft.';
            return;
        }

        $analysis = $this->latestAnalysis($case);
        if (! $this->isCompleted($analysis)) {
            $this->notice = 'A completed Case Assistant analysis is required before preparing a troubleshooting note.';
            return;
        }

        $result = is_array($analysis->result) ? $analysis->result : [];
        $rawActions = $result['suggested_next_actions'] ?? [];
        $rawWarnings = $result['warnings'] ?? [];
        $actions = is_array($rawActions) ? array_values(array_filter($rawActions, 'is_string')) : [];
        $warnings = is_array($rawWarnings) ? array_values(array_filter($rawWarnings, 'is_string')) : [];

        $lines = [
            'AI-assisted troubleshooting review (DRAFT — not evidence of work completed)',
            'Case: '.$case->number,
            'Summary (AI-generated, verify against case evidence): '.trim((string) ($result['summary'] ?? '')),
            '',
            'Suggested checks (NOT yet performed):',
        ];
        foreach (array_slice($actions, 0, 15) as $index => $action) {
            $lines[] = ($index + 1).'. '.trim($action);
        }
        if (! $actions) {
            $lines[] = 'No specific checks supplied; review case manually.';
        }

        if ($warnings) {
            $lines[] = '';
            $lines[] = 'Uncertainties / missing evidence:';
            foreach (array_slice($warnings, 0, 10) as $warning) {
                $lines[] = '- '.trim($warning);
            }
        }

        $lines[] = '';
        $lines[] = 'Engineer findings / tests actually performed (enter verified facts only):';
        $lines[] = '[Add findings here before saving]';
        $lines[] = '';
        $lines[] = 'Next steps / handoff:';
        $lines[] = '[Add owner and next action]';

        $this->draft = mb_substr(implode(PHP_EOL, $lines), 0, 10000);
        $this->sourceAnalysisId = (int) $analysis->id;
        $this->caseSnapshot = $this->snapshot($case);
        $this->confirmed = false;
        $this->notice = 'Draft prepared from the completed Case Assistant analysis. Verify suggestions against the current case before saving.';
    }

    public function save(): void
    {
        $case = $this->currentCase();
        Gate::authorize('update', $case);

        $this->validate([
            'draft' => ['required', 'string', 'min:20', 'max:10000'],
            'confirmed' => ['accepted'],
        ]);

        if ($case->closed_datetime !== null || $case->resolved_datetime !== null) {
            $this->notice = 'This case is already resolved or closed. Nothing was saved.';
            return;
        }

        if (! $this->sourceAnalysisId) {
            $this->notice = 'Prepare a troubleshooting draft first.';
            return;
        }

        DB::transaction(function () use ($case): void {
            $locked = tickets::query()->lockForUpdate()->findOrFail($case->id);
            $analysis = AIAnalysis::query()
                ->where('case_id', $locked->id)
                ->where('type', 'case_assistant')
                ->where('status', 'completed')
                ->find($this->sourceAnalysisId);

            if (! $analysis || ! $this->caseSnapshot
                || ! hash_equals($this->caseSnapshot, $this->snapshot($locked))
                || $this->latestAnalysis($locked)?->id !== $analysis->id
                || $locked->closed_datetime !== null
                || $locked->resolved_datetime !== null) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'draft' => 'The case or analysis changed. Prepare a new draft before saving.',
                ]);
            }

            TaskLogs::query()->create([
                'name' => 'AI-assisted troubleshooting',
                'short_description' => 'Reviewed AI troubleshooting draft · analysis #'.$analysis->id,
                'type' => 'Internal Update',
                'source' => 'ai_assisted',
                'ticket_id' => $locked->id,
                'user_id' => auth()->id(),
                'description' => trim($this->draft).PHP_EOL.PHP_EOL
                    .'Prepared using Case Assistant analysis #'.$analysis->id
                    .'. Reviewed and saved by a staff member; advice is not a confirmed diagnosis.',
                'visible_to_external' => false,
                'require_attention' => false,
            ]);
        });

        $this->draft = '';
        $this->sourceAnalysisId = null;
        $this->caseSnapshot = null;
        $this->confirmed = false;
        $this->notice = 'Reviewed troubleshooting note saved to the internal Case Progress log. The case remains unchanged.';
    }

    public function render()
    {
        $case = $this->currentCase();
        $analysis = $this->latestAnalysis($case);
        $solutionAnalysis = AIAnalysis::query()->where('case_id', $case->id)
            ->where('type', 'troubleshooting_solution')->latest('id')->first();

        return view('ai-management::livewire.troubleshooting-review', [
            'case' => $case,
            'analysis' => $analysis,
            'analysisCurrent' => $this->isCompleted($analysis),
            'analysisOlderThanCase' => $this->caseUpdatedSinceAnalysis($case, $analysis),
            'solutionAnalysis' => $solutionAnalysis,
            'solutionConfigIssue' => app(TroubleshootingSolutionService::class)->configurationIssue(),
        ]);
    }
}
