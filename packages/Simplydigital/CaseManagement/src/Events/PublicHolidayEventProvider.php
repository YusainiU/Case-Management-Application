<?php

namespace Simplydigital\CaseManagement\Events;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use JsonException;
use RuntimeException;
use Simplydigital\CaseManagement\Services\CasePublicHolidayService;
use Simplydigital\EventPlannerManagement\Contracts\PlannerEventProvider;
use Simplydigital\EventPlannerManagement\Contracts\ProvidesPlannerWarnings;
use Simplydigital\EventPlannerManagement\DTO\PlannerEvent;

class PublicHolidayEventProvider implements PlannerEventProvider, ProvidesPlannerWarnings
{
    /** @var list<string> */
    private array $warnings = [];

    public function __construct(private CasePublicHolidayService $holidays) {}

    public function warnings(): array
    {
        return $this->warnings;
    }

    public function events(
        CarbonInterface $start,
        CarbonInterface $end,
        ?int $userId = null,
    ): Collection {
        $this->warnings = [];
        $events = collect();
        if ($start->gte($end)) {
            return $events;
        }

        $timezone = config('app.timezone', 'UTC');
        $from = $start->copy()->timezone($timezone);
        $until = $end->copy()->timezone($timezone);

        try {
            $country = $this->holidays->getCountryCode();
            $lastYear = $until->copy()->subMicrosecond()->year;
            for ($year = $from->year; $year <= $lastYear; $year++) {
                $calendar = $this->holidays->getBankHolidaysForYear($year);
                foreach ($calendar['description'] as $date => $name) {
                    $day = Carbon::createFromFormat('!d-m-Y', $date, $timezone);
                    $nextDay = $day->copy()->addDay();
                    if ($day->gte($until) || $nextDay->lte($from)) {
                        continue;
                    }

                    $events->push(new PlannerEvent(
                        id: 'public-holiday-'.$country.'-'.$day->toDateString(),
                        title: $name,
                        start: $day,
                        end: $nextDay,
                        allDay: true,
                        type: 'public_holiday',
                        description: 'Public holiday ('.$country.')',
                        colour: config('planner.event_types.public_holiday.colour', '#FEF3C7'),
                        text: config('planner.event_types.public_holiday.text', '#92400E'),
                        icon: config('planner.event_types.public_holiday.icon', 'calendar'),
                        metadata: ['country' => $country],
                    ));
                }
            }
        } catch (RuntimeException|InvalidArgumentException|JsonException $exception) {
            report($exception);
            $this->warnings[] = 'Public holidays could not be fully loaded. Check the holiday calendar settings or try again later.';
        }

        return $events->sortBy('start')->values();
    }
}
