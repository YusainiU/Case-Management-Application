<?php

namespace Tests\Unit;

use Tests\TestCase;
use Simplydigital\CaseManagement\Services\SLACalculationService;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\SLALoggerService;
use Carbon\Carbon;

class CaseBuildCalcTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_buildCalcData(): void
    {

        $loggers = new SLALoggerService();
        $loggers->enable();
        $SLACalcService = new SLACalculationService($loggers);

        $caseId = 9; // Replace with an actual case ID from your database
        $sampleCase = tickets::find($caseId); // Replace with an actual case ID from your database

        //===============================================================
        //TEST FUNCTION ONE
        //$fieldToCheck = 'response_time'; // Replace with the field you want to check
        //$ServiceUtility = new CaseTicketUtilityServices();        
        // dump($sampleCase);
        //$this->assertEquals($caseId, $sampleCase->id); // Replace with the expected case ID
        //$calcData = $ServiceUtility->buildCalcData($sampleCase, $fieldToCheck);

        //Example $data structure:
        
            // $calcData = ['
            //     "response_time" =>54000,
            //     "fixed_time" => 21600,
            //     "service_start_time" => 28800,
            //     "service_end_time" => 61200,
            //     "startDate" => 1779971400,
            //     "startDay" => "monday",
            //     "endDay" => "friday",
            //     "createdDay" => "thurday",
            //     "includePublicHoliday" => false,
            // ];
        
        //$calc = $SLACalcService->calcRespondByTime($calcData);
        //$logs = $loggers->getLogs();
        //dump($logs);
        //dump($calc);

        //===============================================================
        //TEST FUNCTION 2
        // $calc = $SLACalcService->recalculateRespondByTime($sampleCase, time());
        // $logs = $loggers->getLogs();
        // dump($logs);
        // dump($calc);

        //===============================================================
        //TEST FUNCTION THREE        
        $from = carbon::parse('2026-06-26 12:00:00');
        $to = carbon::parse('2026-06-29 10:00:00');
        $calc = $SLACalcService->workingMinutesBetween($sampleCase, $from, $to);
        $remaining = $SLACalcService->calculatePauseRemainingValue($sampleCase, carbon::now());
        // REMEMBER TO CHANGE THE FUNCTION TO PUBLIC !!
        //$pub = $SLACalcService->isPublicHoliday($from->timestamp);
        $logs = $loggers->getLogs();
        dump($calc * 60);
        dump("REMAINING: $remaining");
        dump($logs);

        $this->assertNotEmpty($calc);

    }
}
