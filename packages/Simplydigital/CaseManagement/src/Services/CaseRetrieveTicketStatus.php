<?php
/**
 * Case-level SLA and activity status used by Dashboard and Case Information.
 * All aggregated flags are computed for the requested case only.
 */

namespace Simplydigital\CaseManagement\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\tickets;

class CaseRetrieveTicketStatus
{
    private const RELATIONS = [
        'slaTasks.activePause',
        'taskLogs',
        'siteVisits',
    ];

    public function retrieveStatuses(iterable $tickets): array
    {
        $ids = collect($tickets)->pluck('id')->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return ['total' => 0];
        }

        $cases = tickets::query()->whereKey($ids)->with(self::RELATIONS)->get();

        $result = $cases->mapWithKeys(fn (tickets $ticket): array => [
            $ticket->id => $this->buildTicketStatus($ticket),
        ])->all();
        $result['total'] = $cases->count();

        return $result;
    }

    public function retrieveStatus(tickets $ticket): array
    {
        // Reload to avoid stale SLA and pause data on long-lived Livewire models.
        $ticket->unsetRelation('slaTasks');
        $ticket->unsetRelation('taskLogs');
        $ticket->unsetRelation('siteVisits');
        $ticket->load(self::RELATIONS);

        return $this->buildTicketStatus($ticket);
    }

    private function buildTicketStatus(tickets $ticket): array
    {
        $logs = $ticket->taskLogs;
        $visits = $ticket->siteVisits;
        $sla = $ticket->slaTasks;
        $sla?->setRelation('caseTicket', $ticket);

        $urgent = $logs->where('require_attention', true);
        $lastUrgent = $urgent->sortByDesc('created_at')->first();
        $lastCustomer = $logs->where('source', 'external')->sortByDesc('created_at')->first();

        // Case "No Update" means no staff-created internal progress note,
        // not "no log at all" (every newly created case has a system log).
        $internalType = config('case-management.logs.type.internal_update', 'internal_update_log');
        $hasInternalUpdate = $logs->contains(fn ($log): bool =>
            in_array($log->type, [$internalType, 'internal_update'], true)
            || $log->source === 'ai_assisted'
        );

        // The dashboard "Paused" card is a CASE count and must agree with
        // the Paused cases filter (which uses tickets.state). Older records
        // may still have an unfinished SLA pause after the case was moved
        // back to In Progress through a different/manual workflow.
        // Keep the active SLA pause separately to protect SLA calculations.
        $slaPaused = $sla?->activePause !== null;
        $casePaused = $ticket->state === CaseState::Paused;
        $resolved = $ticket->resolved_datetime !== null
            || in_array($ticket->state, [CaseState::Resolved, CaseState::Completed], true);
        $closed = $ticket->closed_datetime !== null || in_array(
            $ticket->state, [CaseState::Closed, CaseState::Cancelled], true
        );
        $active = ! $resolved && ! $closed;

        // A paused SLA is frozen and must not be counted as breached.
        $respondBreach = $active && ! $slaPaused && $sla !== null
            && ($sla->respond_by_breach_at !== null
                || ($sla->respond_by !== null && $sla->respond_by->isPast()
                    && ($sla->responded_at === null || $sla->responded_at->gt($sla->respond_by))));
        $fixBreach = $active && ! $slaPaused && $sla !== null
            && ($sla->fix_by_breach_at !== null
                || ($sla->fix_by !== null && $sla->fix_by->isPast()
                    && ($sla->fixed_at === null || $sla->fixed_at->gt($sla->fix_by))));
        $breached = $respondBreach || $fixBreach;
        $nearBreach = $active && ! $slaPaused && ! $breached && $sla !== null
            && $sla->show_warning_flag;

        $outstandingVisits = $visits->whereNull('offsite_at');

        return [
            'numberOfUrgentLogs' => $urgent->count(),
            'lastUrgentLogCreatedAt' => $this->formatDate($lastUrgent?->created_at),
            'lastCustomerLogCreatedAt' => $this->formatDate($lastCustomer?->created_at),
            'totalVisits' => $outstandingVisits->count(),
            'totalVisited' => $outstandingVisits->whereNotNull('onsite_at')->count(),
            'currentlyEnroute' => $outstandingVisits->whereNull('onsite_at')
                ->whereNotNull('enroute_at')->count(),
            'currentlyOnsite' => $outstandingVisits->whereNotNull('onsite_at')->count(),
            'visitScheduled' => $outstandingVisits->isNotEmpty(),
            'noUpdate' => $active && ! $hasInternalUpdate ? 1 : 0,
            'respondBy' => $this->formatDate($sla?->respond_by),
            'fixBy' => $this->formatDate($sla?->fix_by),
            'respondedAt' => $this->formatDate($sla?->responded_at),
            'fixedAt' => $this->formatDate($sla?->fixed_at),
            'taskBreached' => (int) $breached,
            'breachType' => $fixBreach ? 'Fix By Breach' : ($respondBreach ? 'Respond By Breach' : ''),
            'unassigned' => (int) ($active && $ticket->assigned_to === null),
            'paused' => (int) ($active && $casePaused),
            'closeToBreachWarning' => (int) $nearBreach,
            'resolved' => (int) ($resolved && ! $closed),
            'currentlyWorking' => (bool) ($active && $ticket->currently_working),
            'closed' => $closed,
        ];
    }

    private function formatDate(?\DateTimeInterface $date): ?string
    {
        return $date ? Carbon::instance($date)->format('d-m-Y H:i') : null;
    }
}
