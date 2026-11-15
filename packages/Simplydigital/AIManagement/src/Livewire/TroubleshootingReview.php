<?php

namespace Simplydigital\AIManagement\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Simplydigital\AIManagement\Models\AIAnalysis;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\CaseManagement\Models\tickets;

class TroubleshootingReview extends Component
{
    #[Locked]
    public int $caseId;

    #[Locked]
    public ?int $sourceAnalysisId = null;

    public string $draft = '';

    public bool $confirmed = false;

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
        return AIAnalysis::query()
            ->where('case_id', $case->id)
            ->where('type', 'case_assistant')
            ->where('status', 'completed')
            ->latest('id')
            ->first();
    }

    private function isFresh(tickets $case, AIAnalysis $analysis): bool
    {
        // Avoid showing analysis of an old version as a current plan after
        // the case was subsequently edited by a person or an approved action.
        return $analysis->created_at !== null
            && $case->updated_at !== null
            && $analysis->created_at->greaterThanOrEqualTo($case->updated_at);
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
        if (! $analysis || ! $this->isFresh($case, $analysis)) {
            $this->notice = 'No current completed Case Assistant analysis is available. Re-analyse the case first.';
            return;
        }

        $result = $analysis->result ?? [];
        $actions = array_values(array_filter($result['suggested_next_actions'] ?? [], 'is_string'));
        $warnings = array_values(array_filter($result['warnings'] ?? [], 'is_string'));

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
        $this->confirmed = false;
        $this->notice = 'Draft prepared. Review and edit it before saving an internal progress note.';
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

            if (! $analysis || ! $this->isFresh($locked, $analysis)
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
        $this->confirmed = false;
        $this->notice = 'Reviewed troubleshooting note saved to the internal Case Progress log. The case remains unchanged.';
    }

    public function render()
    {
        $case = $this->currentCase();
        $analysis = $this->latestAnalysis($case);

        return view('ai-management::livewire.troubleshooting-review', [
            'case' => $case,
            'analysis' => $analysis,
            'analysisCurrent' => $analysis ? $this->isFresh($case, $analysis) : false,
        ]);
    }
}
