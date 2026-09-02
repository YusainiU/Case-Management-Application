<?php

namespace Simplydigital\CaseManagement\Events;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Simplydigital\EventPlannerManagement\Contracts\PlannerEventProvider;
use Simplydigital\EventPlannerManagement\DTO\PlannerEvent;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Enums\CaseState;

class SlaExpiryEventProvider implements PlannerEventProvider
{
    public function events(
        CarbonInterface $start,
        CarbonInterface $end,
        ?int $userId = null,
    ): Collection {
        $query = tickets::query()
            ->whereHas('slaTasks', function ($query) use ($start, $end) {
                $query->whereNotNull('respond_by')
                    ->where('active', true)
                    ->where('state', '!=', CaseState::Closed->value)
                    ->whereBetween('respond_by', [
                        $start,
                        $end,
                    ]);
            })
            ->whereNull('closed_datetime');

        if ($userId !== null) {
            $query->where('assigned_to', $userId);
        }

        return $query
            ->get()
            ->map(function (tickets $case) {
                return new PlannerEvent(
                    id: "sla-expiry-{$case->id}",
                    title: "SLA Expiry - Case #{$case->slaTasks->first()->number}",
                    start: $case->slaTasks->first()->respond_by,
                    end: $case->slaTasls->first()->respond_by,
                    type: 'sla_expiry',
                    url: route(
                        'casemanagement.cases.view',
                        $case
                    ),
                    metadata: [
                        'case_id' => $case->id,
                        'severity' => $case->slaTasks->first()->state,
                    ],
                );
            });
    }
}