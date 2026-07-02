<?php

namespace Simplydigital\CaseManagement\Services;

use Carbon\Carbon;
use Simplydigital\CaseManagement\Models\tickets;

class SLACalculationService
{
    private CasePublicHolidayService $casePublicHolidayService;

    private SLALoggerService $logger;

    public array $holidays = [];

    public function __construct(?SLALoggerService $logger = null)
    {
        $this->casePublicHolidayService = new CasePublicHolidayService;
        $this->logger = $logger ?? new SLALoggerService;
        $this->holidays = $this->casePublicHolidayService->getPublicHolidays();
    }

    public function workingMinutesBetween(tickets $ticket, Carbon $from, Carbon $to): int 
    {

        $data = $this->createSlaData($ticket);
        $slaStartDay = $data['startDay'];
        $slaEndDay = $data['endDay'];
        $service_start_time = $data['service_start_time'];
        $service_end_time = $data['service_end_time'];

        // $this->logger->log('WM - initial data', [
        //     'sla_start_day' => $slaStartDay,
        //     'sla_end_day' => $slaEndDay,
        //     'service_start_time' => $service_start_time,
        //     'service_end_time' => $service_end_time,
        //     'include_public_holiday' => $includePublicHoliday,
        //     'from' => $from->format('d-m:Y H:i:s'),
        //     'to' => $to->format('d-m:Y H:i:s'),
        // ]);


        if ($from->gte($to)) {
            return 0;
        }

        $minutes = 0;
        $current = $from->copy();
        $counter = 0;

        while ($current->lt($to)) {

            $counter++;
            $startDay = $current->dayName;

            // $logTitle = "WM - loop $counter";
            // $this->logger->log($logTitle,[
            //     'current' => $current->format('d-m-Y H:i:s'),
            //     'current_timestamp' => $current_ts,
            //     'start_day' => $startDay,
            // ]);

            // Skip non-business days
            $isWorkingDay = CaseManagementUtilityServices::isDayInsideSla(
                $slaStartDay,
                $slaEndDay,
                $startDay,
            );

            
            $isWorkingDay = $this->isBusinessWorkingHours($data, $current);
            //$this->logger->log("$counter - isWorkingDay", $isWorkingDay);

            if (! $isWorkingDay) {
                $current->addDay()->startOfDay();
                continue;
            }

            $serviceStart = gmdate('H:i:s', $service_start_time);
            $serviceStart = Carbon::parse(
                $current->format('Y-m-d').' '.$serviceStart
            );

            $serviceEnd = gmdate('H:i:s', $service_end_time);
            $serviceEnd = Carbon::parse(
                $current->format('Y-m-d').' '.$serviceEnd
            );

            // Before business hours
            if ($current->lt($serviceStart)) {
                $current = $serviceStart;
            }

            // After business hours
            if ($current->gte($serviceEnd)) {
                $current->addDay()->startOfDay();
                //$this->logger->log("$counter - After Business Hour .. next ..", $current->format('d-m-Y H:i:s'));
                continue;
            }

            $segmentEnd = $to->copy();
            //$this->logger->log("$counter - segmentEnd ..", $segmentEnd);

            if ($segmentEnd->gt($serviceEnd)) {
                $segmentEnd = $serviceEnd;
            }

            if ($segmentEnd->gt($current)) {
                $minutes += $current->diffInMinutes($segmentEnd);
            }

            $current = $current->copy()->addDay()->startOfDay();
        }

        //$this->logger->log("WM - Working Minutes ..", $minutes);
        return $minutes;
    }

    public function calculatePauseRemainingValue(tickets $ticket, carbon $pausedDate)
    {
        $sla = $this->createSlaData($ticket);
        $response_time = $sla['response_time']; //in seconds
        $fix_time = $sla['fixed_time']; //in seconds
        //$this->logger->log('paused_time', $pausedDate);
        //$this->logger->log('response_time', $response_time);
        $created_at  = carbon::parse($ticket->created_at);
        //$this->logger->log('created_at', $created_at);
        $slaTimeConsumed = $this->workingMinutesBetween($ticket, $created_at, $pausedDate);
        //$this->logger->log('sla_time_consumed', $slaTimeConsumed);
        $slaTimeConsumed = $slaTimeConsumed * 60; // In Seconds
        //$this->logger->log('sla_time_consumed in seconds', $slaTimeConsumed);
        $slaTimeRemaining = $response_time - $slaTimeConsumed;
        $slaFixTimeRemaining = $fix_time - $slaTimeConsumed;
        //$this->logger->log('calculation: ', "Seconds Remaining = $response_time - $slaTimeConsumed");
        //$this->logger->log('sla_time_remaining', $slaTimeRemaining);

        return [
            'response_time_remaining' => $slaTimeRemaining,
            'fix_time_remaining' => $slaFixTimeRemaining,
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
        $data['startDay'] = strtolower($startDate->dayName);
        return $this->calcRespondByTime($data);
    }

    public function calcRespondByTime(array $data)
    {

        // Example $data structure:
        /*
            array:9 [
            "response_time" => 14400
            "fixed_time" => 21600
            "service_start_time" => 28800
            "service_end_time" => 61200
            "startDate" => 1779532706
            "startDay" => "monday"
            "endDay" => "friday"
            "createdDay" => "saturday"
            "includePublicHoliday" => true
            ]
        */

        $this->logger->log('Initial Data', $data);

        // Set default service end time if not defined
        $serviceEndAt = $data['service_end_time'] ?: 86400;
        $serviceStartAt = $data['service_start_time'] ?: 0;
        $responseTime = $data['response_time'] ?: 0;
        $serviceStartHMS = CaseManagementUtilityServices::secondsToHMS($serviceStartAt);
        $serviceEndHMS = CaseManagementUtilityServices::secondsToHMS($serviceEndAt);
        $responseTimeHMS = CaseManagementUtilityServices::secondsToHMS($responseTime);
        $this->logger->log('Service Start At', $serviceStartHMS);
        $this->logger->log('Service End At', $serviceEndHMS);
        $this->logger->log('Response Time', $responseTimeHMS);

        // Start from the initial date
        $currentDate = $data['startDate'];
        $carryoverSeconds = 0;
        $maxIterations = 20;
        $currentDateYMD = date('Y-m-d', $currentDate);
        $this->logger->log('Starting Calculation Loop', [
            'currentDate' => $currentDateYMD,
            'carryoverSeconds' => $carryoverSeconds,
        ]);

        for ($iteration = 0; $iteration < $maxIterations; $iteration++) {
            $currentDateSeconds = CaseManagementUtilityServices::parseTimeStampToSeconds($currentDate);
            $currentDay = strtolower(date('l', $currentDate));
            $currentCarbonDate = Carbon::createFromTimestamp($currentDate);

            $this->logger->log("Iteration $iteration", [
                'currentDate' => date('Y-m-d', $currentDate),
                'currentDay' => $currentDay,
                'carryoverSeconds' => $carryoverSeconds,
            ]);

            // Check if current day is within SLA working days
            $isWorkingDate = $this->isBusinessWorkingHours($data, $currentCarbonDate);

            if (! $isWorkingDate) {
                $currentDate = $this->rebuildDateTimeUsingTheServiceStart($currentDate, $serviceStartAt);
                $currentDate = strtotime('+1 day', $currentDate);
                $this->logger->log("$iteration: Skipping Non-Working Day", date('Y-m-d', $currentDate));
                continue;
            }

            // Check whether time created is before the service time
            if ($this->checkTimeIsBeforeServiceHours($currentDateSeconds, $serviceStartAt)) {
                $currentDate = $this->rebuildDateTimeUsingTheServiceStart($currentDate, $serviceStartAt);
                $this->logger->log("$iteration: Adjusting Start Time to Service Start", date('Y-m-d H:i:s', $currentDate));
            }

            // Check if current time is after service hours
            if ($this->checkTimeIsAfterServiceHours($currentDateSeconds, $serviceEndAt)) {
                $currentDate = $this->rebuildDateTimeUsingTheServiceStart($currentDate, $serviceStartAt);
                $currentDate = strtotime('+1 day', $currentDate);
                $this->logger->log("$iteration: Adjusting Start Time to Next Day Service Start", date('Y-m-d H:i:s', $currentDate));
            }

            // Calculate the response time
            $result = $this->calculateResponseDateTime(
                $iteration,
                $currentDate,
                $carryoverSeconds,
                $serviceStartAt,
                $serviceEndAt,
                $responseTime
            );

            $this->logger->log("$iteration: Calculation Result", $result);

            if ($result['isComplete']) {
                $this->logger->log("$iteration: Final Result", $result);

                return $this->formatDateTime($result['date'], $result['time']);
                // return $this->logger->getLogs();
            }

            // Update for next iteration
            $carryoverSeconds = $result['carryoverSeconds'];
            $currentDate = strtotime('+1 day', $currentDate);
            $this->logger->log("$iteration: Carryover Seconds", $carryoverSeconds);
            $this->logger->log("$iteration: Next Date", date('Y-m-d', $currentDate));
        }

        // Fallback if max iterations reached (should not happen with proper data)
        return $this->formatDateTime($currentDate, $serviceStartAt);
    }

    private function checkTimeIsBeforeServiceHours(int $currentTime, int $serviceStartAt): bool
    {
        return $currentTime < $serviceStartAt;
    }

    public function checkTimeIsAfterServiceHours(int $currentTime, int $serviceEndAt): bool
    {
        return $currentTime > $serviceEndAt;
    }

    public function rebuildDateTimeUsingTheServiceStart(int $currentDate, int $serviceStartAt): int
    {
        $dateYMD = date('Y-m-d', $currentDate);
        $newDateTime = strtotime("$dateYMD ".CaseManagementUtilityServices::secondsToHMS($serviceStartAt));

        return $newDateTime;
    }

    /**
     * Calculate response date and time for a given day
     */
    private function calculateResponseDateTime(
        int $iteration,
        string $currentDate,
        int $carryoverSeconds,
        int $serviceStartAt,
        int $serviceEndAt,
        int $responseTime)
    {
        $currentTime = CaseManagementUtilityServices::parseTimeStampToSeconds($currentDate);
        $this->logger->log("$iteration: Current Time in Seconds", $currentTime);

        // Handle carryover from previous days
        if ($carryoverSeconds > 0) {
            $initSeconds = $serviceStartAt + $carryoverSeconds;
            $this->logger->log("$iteration: Handling Carryover", [
                'carryoverSeconds' => $carryoverSeconds,
                'initial Seconds' => $initSeconds,
            ]);

            if ($initSeconds <= $serviceEndAt) {
                $this->logger->log("$iteration: Carryover Completes Today", [
                    'date' => date('Y-m-d', $currentDate),
                    'time' => CaseManagementUtilityServices::secondsToHMS($initSeconds),
                ]);

                return [
                    'isComplete' => true,
                    'date' => $currentDate,
                    'time' => $initSeconds,
                ];
            } else {
                $this->logger->log("$iteration: Carryover Continues to Next Day", [
                    'currentDate' => date('Y-m-d', $currentDate),
                    'responseTime' => CaseManagementUtilityServices::secondsToHMS($initSeconds),
                ]);

                return [
                    'isComplete' => false,
                    'carryoverSeconds' => $initSeconds - $serviceEndAt,
                ];
            }
        }

        // No carryover - calculate from current time
        if ($currentTime > $serviceEndAt) {
            // Ticket created after service hours
            $this->logger->log("$iteration: Ticket Created After Service Hours", [
                'currentTime' => CaseManagementUtilityServices::secondsToHMS($currentTime),
                'serviceEndAt' => CaseManagementUtilityServices::secondsToHMS($serviceEndAt),
            ]);

            return [
                'isComplete' => false,
                'carryoverSeconds' => 0,
            ];
        }

        // Calculate response time for current day
        $responseEndTime = max($currentTime, $serviceStartAt) + $responseTime;
        $this->logger->log("$iteration: Calculated Response End Time", CaseManagementUtilityServices::secondsToHMS($responseEndTime));

        if ($responseEndTime > $serviceEndAt) {
            // Response spans to next day
            $this->logger->log("$iteration: Response Spans to Next Day", [
                'responseEndTime' => CaseManagementUtilityServices::secondsToHMS($responseEndTime),
                'serviceEndAt' => CaseManagementUtilityServices::secondsToHMS($serviceEndAt),
            ]);

            return [
                'isComplete' => false,
                'carryoverSeconds' => $responseEndTime - $serviceEndAt,
            ];
        }

        // Response completes today
        $this->logger->log("$iteration: Response Completes Today", [
            'responseEndTime' => CaseManagementUtilityServices::secondsToHMS($responseEndTime),
            'responseDate' => date('Y-m-d', $currentDate),
        ]);

        return [
            'isComplete' => true,
            'date' => $currentDate,
            'time' => $responseEndTime,
        ];
    }

    /**
     * Check if a date is a public holiday
     */
    public function isPublicHoliday(int $date)
    {
        $dmy = date('d-m-Y', $date);

        return \in_array($dmy, $this->holidays);
    }

    /**
     * Check whether datetime provided is inside SLA
     */    
    public function isBusinessWorkingHours(array $data, carbon $datetime)
    {
        //Data is from $this->createSlaData()
        $slaStartDay = $data['startDay'];
        $slaEndDay = $data['endDay'];
        $includePublicHoliday = $data['includePublicHoliday'];
                
        $isWorkingDay = CaseManagementUtilityServices::isDayInsideSla(
            $slaStartDay,
            $slaEndDay,
            $datetime->dayName,
        );

        if($includePublicHoliday){
            $isPublicHoliday = $this->isPublicHoliday($datetime->timestamp);
            if($isPublicHoliday){
                $isWorkingDay = false;
            }
        }
        
        return $isWorkingDay;
    }

    /**
     * Format the final date and time
     */
    private function formatDateTime(string $date, int $timeInSeconds)
    {
        $formattedDate = date('Y-m-d', $date);
        $timeParts = CaseManagementUtilityServices::parseSeconds($timeInSeconds);
        $formattedTime = "{$timeParts['hours']}:{$timeParts['mins']}:{$timeParts['seconds']}";

        return "$formattedDate $formattedTime";
    }
}
