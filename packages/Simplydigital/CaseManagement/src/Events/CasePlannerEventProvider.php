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
                    title: "Case #{$case->number}",
                    start: $case->created_at,
                    end: $case->created_at,
                    type: 'case',
                    url: route(
                        'casemanagement.case.view',
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