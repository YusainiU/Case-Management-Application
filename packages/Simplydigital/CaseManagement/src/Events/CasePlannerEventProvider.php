<?php

namespace Simplydigital\CaseManagement\Events;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Simplydigital\EventPlannerManagement\Contracts\PlannerEventProvider;
use Simplydigital\EventPlannerManagement\DTO\PlannerEvent;
use Simplydigital\CaseManagement\Models\tickets;

class CasePlannerEventProvider implements PlannerEventProvider
{
    public function events(
        CarbonInterface $start,
        CarbonInterface $end,
        ?int $userId = null,
    ): Collection {
        $query = tickets::query()
            ->whereBetween('created_at', [
                $start,
                $end,
            ]);

        if ($userId !== null) {
            $query->where('assigned_to', $userId);
        }

        return $query
            ->get()
            ->map(function (tickets $case) {
                return new PlannerEvent(
                    id: "case-{$case->id}",
                    title: "Case #{$case->id} - {$case->title}",
                    start: $case->scheduled_at,
                    end: $case->scheduled_end_at,
                    type: 'case',
                    url: route(
                        'casemanagement.cases.view',
                        $case
                    ),
                    description: $case->description,
                    metadata: [
                        'case_id' => $case->id,
                    ],
                );
            });
    }
}