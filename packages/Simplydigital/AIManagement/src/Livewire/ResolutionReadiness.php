<?php

namespace Simplydigital\AIManagement\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseTicketServices;
use Simplydigital\CaseManagement\Services\CaseUserServices;
use Illuminate\Validation\Rule;

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
    public bool $resolveCase = false;
    public ?string $resolutionType = null;
    public ?int $resolvedBy = null;
    public ?string $resolvedAt = null;
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
            && $case->deleted_at === null
            && $case->state instanceof CaseState
            && in_array($case->state, CaseState::openStates(), true);
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
        $this->resolveCase = false;
        $this->resolutionType = null;
        $this->resolvedBy = null;
        $this->resolvedAt = now()->format('Y-m-d\\TH:i');
        $this->notice = 'Resolution handoff prepared. Record actual work and verification before saving.';
    }

    public function save(): void
    {
        $case = $this->currentCase();
        Gate::authorize('update', $case);

        $allowedResolutions = array_keys(config('case-management.ticketManagement.resolution', []));
        $allowedResolvers = array_keys(CaseUserServices::getAllInternalUserIds());

        $this->validate([
            'resolveCase' => ['boolean'],
            'resolutionType' => [
                Rule::requiredIf($this->resolveCase), 'nullable', 'string',
                Rule::in($allowedResolutions),
            ],
            'resolvedBy' => [
                Rule::requiredIf($this->resolveCase), 'nullable', 'integer',
                Rule::in($allowedResolvers),
            ],
            'resolvedAt' => [
                Rule::requiredIf($this->resolveCase), 'nullable', 'date_format:Y-m-d\\TH:i',
                'before_or_equal:now',
            ],
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

        $resolved = $this->resolveCase;

        DB::transaction(function () use ($case): void {
            $locked = tickets::query()->lockForUpdate()->findOrFail($case->id);
            $source = $this->sourceNote($locked, (int) $this->sourceLogId);

            if (! $this->mayPrepare($locked) || ! $source
                || ! hash_equals($this->snapshotHash, $this->snapshot($locked, $source))) {
                throw ValidationException::withMessages([
                    'sourceLogId' => 'The case or supporting note changed. Prepare a fresh handoff.',
                ]);
            }

            if ($this->resolveCase && ! $locked->slaTasks()->exists()) {
                throw ValidationException::withMessages([
                    'resolveCase' => 'A case SLA task is required before using the standard resolution process.',
                ]);
            }

            $resolutionNotes = 'Reviewed troubleshooting progress log #'.$source->number.PHP_EOL
                .'Fix actually applied:'.PHP_EOL.trim($this->fixApplied).PHP_EOL.PHP_EOL
                .'Verification actually performed:'.PHP_EOL.trim($this->verification).PHP_EOL.PHP_EOL
                .'Remaining risks / follow-up:'.PHP_EOL
                .(trim($this->remainingRisks) ?: 'None reported by reviewing staff.');

            if ($this->resolveCase) {
                // Use the exact same service as the Close & Resolve form. That
                // service updates SLA, writes the standard log and emails the
                // customer. Never emulate it with a bare state update.
                $success = app(CaseTicketServices::class)->resolveCase($locked, [
                    'resolved_datetime' => $this->resolvedAt,
                    'resolved_by' => (int) $this->resolvedBy,
                    'resolution' => $this->resolutionType,
                    'resolution_details' => $resolutionNotes,
                ]);
                if (! $success) {
                    throw ValidationException::withMessages([
                        'resolveCase' => 'The normal resolution process could not finish. No handoff was saved.',
                    ]);
                }
            }

            TaskLogs::query()->create([
                'name' => 'Resolution readiness handoff',
                'short_description' => 'Reviewed resolution handoff · progress log #'.$source->number,
                'type' => 'Internal Update',
                'source' => 'ai_resolution_review',
                'user_id' => auth()->id(),
                'ticket_id' => $locked->id,
                'description' => ($this->resolveCase
                    ? 'Staff-authorised resolution was applied through the standard Close & Resolve service.'
                    : 'Resolution handoff for human review (not a resolution event).')
                    .PHP_EOL.$resolutionNotes.PHP_EOL.PHP_EOL
                    .($this->resolveCase
                        ? 'The normal resolution process updated the case and SLA and notified the customer.'
                        : 'Recorded by staff; the case was not resolved.'),
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
        $this->resolveCase = false;
        $this->resolutionType = null;
        $this->resolvedBy = null;
        $this->resolvedAt = null;
        $this->notice = $resolved
            ? 'Case resolved through the standard Close & Resolve process. SLA updated and customer notification triggered.'
            : 'Internal resolution handoff saved to Case Progress. The case remains unresolved.';
        $this->dispatch('reloadCaseStatus');
    }

    public function render()
    {
        $case = $this->currentCase();

        return view('ai-management::livewire.resolution-readiness', [
            'case' => $case,
            'resolutionOptions' => config('case-management.ticketManagement.resolution', []),
            'resolverOptions' => CaseUserServices::getAllInternalUserIds(),
            'troubleshootingNotes' => TaskLogs::query()
                ->where('ticket_id', $case->id)
                ->where('source', 'ai_assisted')
                ->with('user')->latest('id')->limit(25)->get(),
        ]);
    }
}
