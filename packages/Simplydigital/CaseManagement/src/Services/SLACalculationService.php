<?php

namespace Simplydigital\CaseManagement\Services;

use Simplydigital\CaseManagement\Services\CasePublicHolidayService;
use Simplydigital\CaseManagement\Services\SLALoggerService;

class SLACalculationService
{
    private CasePublicHolidayService $casePublicHolidayService;
    private SLALoggerService $logger;
    public array $holidays = [];

    public function __construct(?SLALoggerService $logger = null)
    {
        $this->casePublicHolidayService = new CasePublicHolidayService();
        $this->logger = $logger ?? new SLALoggerService();
        $this->holidays = $this->casePublicHolidayService->getPublicHolidays();
    }

    public function calcRespondByTime(array $data)
    {

        //Example $data structure:
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
        $this->logger->log('Service Start At', CaseManagementUtilityServices::secondsToHMS($serviceStartAt));
        $this->logger->log('Service End At', CaseManagementUtilityServices::secondsToHMS($serviceEndAt));
        $this->logger->log('Response Time', CaseManagementUtilityServices::secondsToHMS($responseTime));

        // Start from the initial date
        $currentDate = $data['startDate'];
        $carryoverSeconds = 0;
        $maxIterations = 20;
        $this->logger->log('Starting Calculation Loop', [
            'currentDate' => date('Y-m-d', $currentDate),
            'carryoverSeconds' => $carryoverSeconds,
        ]);

        for ($iteration = 0; $iteration < $maxIterations; $iteration++) {
            $currentDay = strtolower(date('l', $currentDate));
            $this->logger->log("Iteration $iteration", [
                'currentDate' => date('Y-m-d', $currentDate),
                'currentDay' => $currentDay,
                'carryoverSeconds' => $carryoverSeconds,
            ]);

            // Check if current day is within SLA working days
            $isWorkingDay = CaseManagementUtilityServices::isDayInsideSla(
                $currentDate,
                $data['startDay'],
                $data['endDay'],
                $currentDay,
                $data['startDay']
            );

            if (! $isWorkingDay) {
                $currentDate = strtotime('+1 day', $currentDate);
                $this->logger->log("$iteration: Skipping Non-Working Day", date('Y-m-d', $currentDate));
                continue;
            }

            // Skip public holidays if required
            if (! $data['includePublicHoliday'] && $this->isPublicHoliday($currentDate)) {
                $currentDate = strtotime('+1 day', $currentDate);
                $this->logger->log("$iteration: Skipping Public Holiday", date('Y-m-d', $currentDate));
                continue;
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
                    'currentDate' => date('Y-m-d', $currentDate),
                    'responseTime' => CaseManagementUtilityServices::secondsToHMS($initSeconds),
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
        if ($currentTime >= $serviceEndAt) {
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
    private function isPublicHoliday(string $date)
    {
        $dmy = date('d-m-Y', $date);

        return \in_array($dmy, $this->holidays);
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
