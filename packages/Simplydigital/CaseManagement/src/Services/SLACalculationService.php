<?php

namespace Simplydigital\CaseManagement\Services;

use Carbon\Carbon;
use Simplydigital\CaseManagement\Models\tickets;

class SLACalculationService
{
    private CasePublicHolidayService $casePublicHolidayService;

    private SLALoggerService $logger;

    public array $holidays = [];

    /** @var array<int, array<int, string>> */
    private array $holidaysByYear = [];

    public function __construct(?SLALoggerService $logger = null)
    {
        $this->casePublicHolidayService = new CasePublicHolidayService;
        $this->logger = $logger ?? new SLALoggerService;
        $this->holidays = $this->casePublicHolidayService->getPublicHolidays();
    }

    /**
     * Count whole SLA business minutes between two instants.
     * All deadline/pause arithmetic uses workingSecondsBetween() to avoid
     * silently discarding up to 59 seconds at each pause.
     */
    public function workingMinutesBetween(tickets $ticket, Carbon $from, Carbon $to): int
    {
        return intdiv($this->workingSecondsBetween($ticket, $from, $to), 60);
    }

    public function workingSecondsBetween(tickets $ticket, Carbon $from, Carbon $to): int
    {
        return $this->workingSecondsBetweenData($this->createSlaData($ticket), $from, $to);
    }

    private function workingSecondsBetweenData(array $data, Carbon $from, Carbon $to): int
    {
        if ($from->gte($to)) {
            return 0;
        }

        [$startSeconds, $endSeconds] = $this->serviceWindow($data);
        $timezone = config('app.timezone', 'UTC');
        $cursor = $from->copy()->timezone($timezone)->startOfDay();
        $end = $to->copy()->timezone($timezone);
        $seconds = 0;

        while ($cursor->lt($end)) {
            if ($this->isBusinessWorkingHours($data, $cursor)) {
                $workStart = $this->localServiceBoundary($cursor, $startSeconds);
                $workEnd = $this->localServiceBoundary($cursor, $endSeconds);
                $segmentStart = $from->greaterThan($workStart) ? $from : $workStart;
                $segmentEnd = $end->lessThan($workEnd) ? $end : $workEnd;

                if ($segmentEnd->gt($segmentStart)) {
                    $seconds += (int) $segmentStart->diffInSeconds($segmentEnd);
                }
            }

            $cursor->addDay()->startOfDay();
        }

        return $seconds;
    }

    public function calculatePauseRemainingValue(tickets $ticket, Carbon $pausedDate): array
    {
        $task = $ticket->slaTasks;
        if (! $task || ! $task->respond_by || ! $task->fix_by) {
            throw new \DomainException(
                'An SLA task with response and fix deadlines is required to freeze remaining time.'
            );
        }

        // The CURRENT deadlines already include any previous pause periods.
        // Never restart a calculation from the original case creation date.
        return [
            'response_time_remaining' => $this->workingSecondsBetween(
                $ticket, $pausedDate, $task->respond_by
            ),
            'fix_time_remaining' => $this->workingSecondsBetween(
                $ticket, $pausedDate, $task->fix_by
            ),
        ];
    }

    public function createSlaData(tickets $ticket)
    {
        $sla = $ticket->slaApplication->serviceLevelAgreement;
        $data = [
            'response_time' => $sla->response_time,
            'fixed_time' => $sla->fixed_time,
            'service_start_time' => $sla->service_start_time,
            'service_end_time' => $sla->service_end_time,
            'startDay' => $sla->start_day->value,
            'endDay' => $sla->end_day->value,
            'includePublicHoliday' => $sla->include_public_holiday,
        ];

        return $data;
    }

    public function recalculateRespondByTime(tickets $ticket, int $startDate)
    {
        $sla = $ticket->slaApplication->serviceLevelAgreement;
        $createdDay = strtolower(date('l', $startDate));
        $data = $this->createSlaData($ticket);
        $data['startDate'] = $startDate;
        $data['createdDay'] = $createdDay;

        return $this->calcRespondByTime($data);
    }

    public function calcCustomRespondByTime(tickets $ticket, int $responsetime, carbon $startDate)
    {
        //respondby in seconds
        $data = $this->createSlaData($ticket);
        $data['response_time'] = $responsetime;
        $data['startDate'] = $startDate->timestamp;
        // Do not overwrite SLA startDay: it defines the contractual working
        // week, not the weekday on which this case happened to resume.
        return $this->calcRespondByTime($data);
    }

    /**
     * Calculate the instant after consuming an SLA allowance expressed in
     * seconds, counting only configured working-day and service-hour intervals.
     *
     * Never silently fall back to an unrelated date if a long SLA is not
     * fulfilled. The maximum calendar span is generous but bounded.
     */
    public function calcRespondByTime(array $data): string
    {
        if (! isset($data['startDate'], $data['response_time'])
            || ! is_numeric($data['response_time'])
            || $data['response_time'] < 0) {
            throw new \InvalidArgumentException('SLA startDate and a nonnegative duration are required.');
        }

        [$startSeconds, $endSeconds] = $this->serviceWindow($data);
        $timezone = config('app.timezone', 'UTC');
        $start = Carbon::createFromTimestamp((int) $data['startDate'], $timezone);
        $remaining = (int) $data['response_time'];

        if ($remaining === 0) {
            return $start->format('Y-m-d H:i:s');
        }

        $day = $start->copy()->startOfDay();
        // Allow large legitimate SLAs, but never return a fake deadline if
        // an impossible calendar / duration was configured.
        for ($i = 0; $i < 3660; $i++) {
            if ($this->isBusinessWorkingHours($data, $day)) {
                $serviceStart = $this->localServiceBoundary($day, $startSeconds);
                $serviceEnd = $this->localServiceBoundary($day, $endSeconds);
                $from = $start->greaterThan($serviceStart) ? $start : $serviceStart;

                if ($from->lt($serviceEnd)) {
                    $available = (int) $from->diffInSeconds($serviceEnd);
                    if ($remaining <= $available) {
                        return $from->copy()->addSeconds($remaining)
                            ->format('Y-m-d H:i:s');
                    }

                    $remaining -= $available;
                }
            }

            $day->addDay()->startOfDay();
        }

        throw new \DomainException(
            'SLA deadline exceeds the supported ten-year calculation horizon.'
        );
    }

    /**
     * Construct a local wall-clock SLA boundary, including DST change days.
     * Adding elapsed seconds to local midnight would incorrectly move the
     * 09:00 service opening to 10:00 after a spring-forward transition.
     */
    private function localServiceBoundary(Carbon $day, int $seconds): Carbon
    {
        if ($seconds === 86400) {
            return $day->copy()->addDay()->startOfDay();
        }

        return $day->copy()->setTime(
            intdiv($seconds, 3600),
            intdiv($seconds % 3600, 60),
            $seconds % 60
        );
    }

    private function serviceWindow(array $data): array
    {
        $start = (int) ($data['service_start_time'] ?? 0);
        $end = (int) ($data['service_end_time'] ?? 86400);

        // Legacy zero end-time means end-of-day; start at zero is valid.
        if ($end === 0) {
            $end = 86400;
        }

        // Overnight service windows need explicit day/holiday rules and
        // cannot safely be calculated by this same-day SLA algorithm.
        if ($start < 0 || $end > 86400 || $start >= $end) {
            throw new \InvalidArgumentException(
                'Invalid SLA service hours: a positive same-day working window is required.'
            );
        }

        return [$start, $end];
    }

    private function checkTimeIsBeforeServiceHours(int $currentTime, int $serviceStartAt): bool
    {
        return $currentTime < $serviceStartAt;
    }

    public function checkTimeIsAfterServiceHours(int $currentTime, int $serviceEndAt): bool
    {
        return $currentTime >= $serviceEndAt;
    }

    public function rebuildDateTimeUsingTheServiceStart(int $currentDate, int $serviceStartAt): int
    {
        return Carbon::createFromTimestamp($currentDate, config('app.timezone', 'UTC'))
            ->startOfDay()->addSeconds($serviceStartAt)->timestamp;
    }

    /**
     * Check if a date is a public holiday
     */
    public function isPublicHoliday(int $date): bool
    {
        $dateTime = Carbon::createFromTimestamp($date, config('app.timezone', 'UTC'));
        $dateString = $dateTime->format('d-m-Y');

        // A service may be used for historic cases or deadlines well beyond
        // the current year. Reload the applicable year rather than relying
        // on the constructor's current-year +/- one calendar.
        $year = $dateTime->year;
        $this->holidaysByYear[$year] ??=
            $this->casePublicHolidayService->getBankHolidaysForYear($year)['dmy'];

        return in_array($dateString, $this->holidaysByYear[$year], true);
    }

    /**
     * This method is historic API naming: it answers whether a DATE is an
     * eligible SLA working day, not whether a time is within service hours.
     */
    public function isBusinessWorkingHours(array $data, Carbon $datetime): bool
    {
        $workingDay = CaseManagementUtilityServices::isDayInsideSla(
            $data['startDay'],
            $data['endDay'],
            $datetime->dayName
        );

        return $workingDay && (! ($data['includePublicHoliday'] ?? false)
            || ! $this->isPublicHoliday($datetime->timestamp));
    }

}
