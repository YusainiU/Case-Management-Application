<?php

namespace Simplydigital\CaseManagement\Events;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Simplydigital\EventPlannerManagement\Contracts\PlannerEventProvider;
use Simplydigital\EventPlannerManagement\DTO\PlannerEvent;
use Simplydigital\CaseManagement\Models\TaskSiteVisit;

class SiteVisitEventProvider implements PlannerEventProvider
{
    public function events(
        CarbonInterface $start,
        CarbonInterface $end,
        ?int $userId = null,
    ): Collection {
        $query = TaskSiteVisit::query()
            ->where('visit_scheduled_at', '!=', null)
            ->whereBetween('visit_scheduled_at', [
                $start,
                $end,
            ])
            ->where('active', true);

        if ($userId !== null) {
            $query->where('assigned_to', $userId);
        }

        return $query
            ->get()
            ->map(fn (TaskSiteVisit $visit) => new PlannerEvent(
                id: "site-visit-{$visit->ticket->number}",
                title: "Site Visit - {$visit->ticket->customer->name}",
                start: $visit->visit_scheduled_at,
                end: $visit->visit_scheduled_at,
                type: 'site_visit',
                url: route(
                    'casemanagement.case.view',
                    $visit->ticket
                ),
                metadata: [
                    'case_id' => $visit->ticket->id,
                    'visit_id' => $visit->id,
                ],
            ));
    }
}