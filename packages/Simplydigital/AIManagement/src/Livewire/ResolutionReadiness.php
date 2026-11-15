<?php

namespace Simplydigital\AIManagement\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\CaseManagement\Models\tickets;

class ResolutionReadiness extends Component
{
    #[Locked]
    public int $caseId;

    #[Locked]
    public ?string $snapshotHash = null;

    public ?int $sourceLogId = null;
    public string $fixApplied = '';
    public string $verification = '';
    public string $remainingRisks = '';
    public bool $verified = false;
    public ?string $notice = null;

    public function mount(tickets $case): void
    {
        $this->caseId = (int) $case->getKey();
        $this->currentCase();
    }

    private function currentCase(): tickets
    {
        $case = tickets::query()->findOrFail($this->caseId);
        Gate::authorize('view', $case);

        return $case;
    }

    private function sourceNote(tickets $case, int $id): ?TaskLogs
    {
        return TaskLogs::query()
            ->where('ticket_id', $case->id)
            ->where('source', 'ai_assisted')
            ->find($id);
    }

    private function snapshot(tickets $case, TaskLogs $note): string
    {
        return hash('sha256', json_encode([
            $case->getKey(),
            $case->getRawOriginal('updated_at'),
            $case->getRawOriginal('state'),
            $case->getRawOriginal('short_description'),
            $case->getRawOriginal('description'),
            $case->getRawOriginal('assigned_to'),
            $case->getRawOriginal('resolved_datetime'),
            $case->getRawOriginal('closed_datetime'),
            $note->getKey(),
            $note->getRawOriginal('updated_at'),
            $note->getRawOriginal('description'),
        ], JSON_THROW_ON_ERROR));
    }

    private function mayPrepare(tickets $case): bool
    {
        return $case->closed_datetime === null && $case->resolved_datetime === null
            && $case->deleted_at === null;
    }

    public function prepare(): void
    {
        $case = $this->currentCase();
        Gate::authorize('update', $case);

        if (! $this->mayPrepare($case)) {
            $this->notice = 'Resolved or closed cases cannot receive a resolution handoff.';
            return;
        }

        $this->validate(['sourceLogId' => ['required', 'integer', 'min:1']]);

        $note = $this->sourceNote($case, $this->sourceLogId);
        if (! $note) {
            throw ValidationException::withMessages([
                'sourceLogId' => 'Select a valid reviewed troubleshooting note for this case.',
            ]);
        }

        $this->snapshotHash = $this->snapshot($case, $note);
        $this->fixApplied = '';
        $this->verification = '';
        $this->remainingRisks = '';
        $this->verified = false;
        $this->notice = 'Resolution handoff prepared. Record actual work and verification before saving.';
    }

    public function save(): void
    {
        $case = $this->currentCase();
        Gate::authorize('update', $case);

        $this->validate([
            'sourceLogId' => ['required', 'integer', 'min:1'],
            'fixApplied' => ['required', 'string', 'min:15', 'max:3000'],
            'verification' => ['required', 'string', 'min:15', 'max:3000'],
            'remainingRisks' => ['nullable', 'string', 'max:2000'],
            'verified' => ['accepted'],
        ]);

        if (! $this->mayPrepare($case)) {
            $this->notice = 'This case is already resolved or closed. Nothing was saved.';
            return;
        }

        if (! $this->snapshotHash) {
            throw ValidationException::withMessages([
                'sourceLogId' => 'Prepare a resolution handoff before saving.',
            ]);
        }

        DB::transaction(function () use ($case): void {
            $locked = tickets::query()->lockForUpdate()->findOrFail($case->id);
            $source = $this->sourceNote($locked, (int) $this->sourceLogId);

            if (! $this->mayPrepare($locked) || ! $source
                || ! hash_equals($this->snapshotHash, $this->snapshot($locked, $source))) {
                throw ValidationException::withMessages([
                    'sourceLogId' => 'The case or supporting note changed. Prepare a fresh handoff.',
                ]);
            }

            TaskLogs::query()->create([
                'name' => 'Resolution readiness handoff',
                'short_description' => 'Reviewed resolution handoff · progress log #'.$source->number,
                'type' => 'Internal Update',
                'source' => 'ai_resolution_review',
                'user_id' => auth()->id(),
                'ticket_id' => $locked->id,
                'description' => 'Resolution handoff for human review (not a resolution event).'.PHP_EOL
                    .'Reviewed troubleshooting progress log: #'.$source->number.PHP_EOL.PHP_EOL
                    .'Fix actually applied:'.PHP_EOL.trim($this->fixApplied).PHP_EOL.PHP_EOL
                    .'Verification actually performed:'.PHP_EOL.trim($this->verification).PHP_EOL.PHP_EOL
                    .'Remaining risks / follow-up:'.PHP_EOL
                    .(trim($this->remainingRisks) ?: 'None reported by reviewing staff.').PHP_EOL.PHP_EOL
                    .'Recorded by a staff reviewer. Use the existing Resolve/Close form '
                    .'to make an authorised resolution decision.',
                'visible_to_external' => false,
                'require_attention' => false,
            ]);
        });

        $this->snapshotHash = null;
        $this->sourceLogId = null;
        $this->fixApplied = '';
        $this->verification = '';
        $this->remainingRisks = '';
        $this->verified = false;
        $this->notice = 'Internal resolution handoff saved to Case Progress. The case remains unresolved.';
    }

    public function render()
    {
        $case = $this->currentCase();

        return view('ai-management::livewire.resolution-readiness', [
            'case' => $case,
            'troubleshootingNotes' => TaskLogs::query()
                ->where('ticket_id', $case->id)
                ->where('source', 'ai_assisted')
                ->with('user')->latest('id')->limit(25)->get(),
        ]);
    }
}
