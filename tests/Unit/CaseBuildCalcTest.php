<?php

namespace Tests\Unit;

use Tests\TestCase;
use Simplydigital\CaseManagement\Services\CaseTicketUtilityServices;
use Simplydigital\CaseManagement\Services\SLACalculationService;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\SLALoggerService;

class CaseBuildCalcTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_buildCalcData(): void
    {

        // $caseId = 2; // Replace with an actual case ID from your database
        // $fieldToCheck = 'response_time'; // Replace with the field you want to check
        // $ServiceUtility = new CaseTicketUtilityServices();
        // $sampleCase = tickets::find($caseId); // Replace with an actual case ID from your database
        // // dump($sampleCase);
        // $this->assertEquals($caseId, $sampleCase->id); // Replace with the expected case ID
        // $calcData = $ServiceUtility->buildCalcData($sampleCase, $fieldToCheck);

        //Example $data structure:
        
            $calcData = [
                "response_time" =>54000,
                "fixed_time" => 21600,
                "service_start_time" => 28800,
                "service_end_time" => 61200,
                "startDate" => 1779971400,
                "startDay" => "monday",
                "endDay" => "friday",
                "createdDay" => "thurday",
                "includePublicHoliday" => false,
            ];
        


        $loggers = new SLALoggerService();
        $loggers->enable();
        $SLACalcService = new SLACalculationService($loggers);
        $calc = $SLACalcService->calcRespondByTime($calcData);
        $logs = $loggers->getLogs();
        dump($logs);
        dump($calc);

        $this->assertNotEmpty($calc);
    }
}
