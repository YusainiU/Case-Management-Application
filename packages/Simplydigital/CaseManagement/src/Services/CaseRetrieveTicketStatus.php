<?php
/**
 * This file is part of the Simplydigital Case Management System.
 * HOW TO USE THIS SERVICE:
 * 1. Inject the CaseRetrieveTicketStatus service into your controller or service where you want to retrieve the ticket status.
 * 2. Use the retrieveStatuses method to get the status of multiple tickets at once by passing an iterable of ticket models.
 * 3. Use the retrieveStatus method to get the status of a single ticket by passing a ticket model.
 * 4. The methods will return an array containing the status information for the tickets, including logs, visits, SLA details, and overall ticket status.
 * Example usage:
 * $ticketStatuses = $caseRetrieveTicketStatus->retrieveStatuses($tickets);
 * To retrieve the total number of breached tasks across multiple tickets, you can sum the 'taskBreached' values from the returned statuses:
 *      totalBreaches = collect($statuses)->sum('taskBreached');
*/

namespace Simplydigital\CaseManagement\Services;

use Carbon\Carbon;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\tickets;

class CaseRetrieveTicketStatus
{
    public function retrieveStatuses(iterable $tickets): array
    {
        /*
        |--------------------------------------------------------------------------
        | Eager load once
        |--------------------------------------------------------------------------
        */

        $tickets = tickets::query()
            ->whereIn(
                'id',
                collect($tickets)->pluck('id')
            )
            ->with([
                'slaTasks:id,ticket_id,respond_by,fix_by,responded_at,fixed_at,breached_at,fix_by_breach_at,respond_by_breach_at',

                'taskLogs:id,ticket_id,created_at,source,require_attention',

                'siteVisits:id,ticket_id,onsite_at,enroute_at,offsite_at',
            ])
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Transform
        |--------------------------------------------------------------------------
        */

        return $tickets
            ->mapWithKeys(fn (tickets $ticket) => [
                $ticket->id => $this->buildTicketStatus($ticket),
            ])
            ->toArray();
    }

    public function retrieveStatus(tickets $ticket): array
    {
        /*
        |--------------------------------------------------------------------------
        | Load only what is needed
        |--------------------------------------------------------------------------
        */

        $ticket->loadMissing([
            'slaTasks:id,ticket_id,respond_by,fix_by,responded_at,fixed_at,breached_at,fix_by_breach_at,respond_by_breach_at',
            'taskLogs:id,ticket_id,created_at,source,require_attention',
            'siteVisits:id,ticket_id,onsite_at,enroute_at,offsite_at',            
        ]);        
        return $this->buildTicketStatus($ticket);
    }

    private function buildTicketStatus(tickets $ticket): array
    {

        /*
        |--------------------------------------------------------------------------
        | Task Logs Aggregates
        |--------------------------------------------------------------------------
        */

        $urgentLogsQuery = $ticket->taskLogs()
            ->where('require_attention', true);

        $lastUrgentLog = (clone $urgentLogsQuery)
            ->latest('created_at')
            ->first(['created_at']);

        $lastCustomerLog = $ticket->taskLogs()
            ->where('source', 'external')
            ->latest('created_at')
            ->first(['created_at']);

        $noUpdate = !$ticket::doesntHave('taskLogs')->count() ?? 1;

        /*
        |--------------------------------------------------------------------------
        | SLA Tasks
        |--------------------------------------------------------------------------
        */

        $task = $ticket->slaTasks;

        $taskBreachedCount = $ticket->slaTasks()
            ->breached()
            ->count();

        $hasFixByBreach = $ticket->slaTasks()
            ->fixBreached()
            ->count();

        $hasRespondByBreach = $ticket->slaTasks()
            ->whereNotNull('respond_by_breach_at')
            ->exists();

        $paused = $ticket->slaTasks()
            ->paused()
            ->count();

        $unassigned = !$ticket->assigned ? 1:0;

        /*
        |--------------------------------------------------------------------------
        | SLA Warning
        |--------------------------------------------------------------------------
        */
        
        $breachWarning = $ticket->slaTasks()
            ->showWarningFlag()
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Site Visits Aggregates
        |--------------------------------------------------------------------------
        */

        $visitsQuery = $ticket->siteVisits();

        $totalVisits = (clone $visitsQuery)->count();

        $totalVisited = (clone $visitsQuery)
            ->whereNotNull('onsite_at')
            ->count();

        $currentlyEnroute = (clone $visitsQuery)
            ->whereNull('onsite_at')
            ->whereNotNull('enroute_at')
            ->count();

        $currentlyOnsite = (clone $visitsQuery)
            ->whereNotNull('onsite_at')
            ->whereNull('offsite_at')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return [
            /*
            |--------------------------------------------------------------------------
            | Logs
            |--------------------------------------------------------------------------
            */

            'numberOfUrgentLogs' => $urgentLogsQuery->count(),

            'lastUrgentLogCreatedAt' => $this->formatDate(
                optional($lastUrgentLog)->created_at
            ),

            'lastCustomerLogCreatedAt' => $this->formatDate(
                optional($lastCustomerLog)->created_at
            ),

            /*
            |--------------------------------------------------------------------------
            | Visits
            |--------------------------------------------------------------------------
            */

            'totalVisits' => $totalVisits,

            'totalVisited' => $totalVisited,

            'currentlyEnroute' => $currentlyEnroute,

            'currentlyOnsite' => $currentlyOnsite,

            'visitScheduled' => $totalVisits > 0,

            /*
            |--------------------------------------------------------------------------
            | SLA
            |--------------------------------------------------------------------------
            */

            'noUpdate' => $noUpdate,

            'respondBy' => $this->formatDate(optional($task)->respond_by),

            'fixBy' => $this->formatDate(optional($task)->fix_by),

            'respondedAt' => $this->formatDate(optional($task)->responded_at),

            'fixedAt' => $this->formatDate(optional($task)->fixed_at),

            'taskBreached' => $taskBreachedCount,

            'breachType' => match (true) {
                $hasFixByBreach => 'Fix By Breach',
                $hasRespondByBreach => 'Respond By Breach',
                default => '',
            },

            /*
            |--------------------------------------------------------------------------
            | Ticket Status
            |--------------------------------------------------------------------------
            */

            'unassigned' => $unassigned,

            'paused' => $paused,

            'closeToBreachWarning' => $breachWarning,

            'resolved' => $this->formatDate($ticket->resolved_datetime),

            'currentlyWorking' => (bool) $ticket->currently_working,

            'closed' => $ticket->state === CaseState::Cancelled->value,
        ];
    }

    private function formatDate(?string $date = null): ?string
    {
        if (! $date) {
            return null;
        }

        return Carbon::parse($date)->format('d-m-Y H:i');
    }

}
