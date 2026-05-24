<?php

namespace Tests\Unit;

use Tests\TestCase;
use Simplydigital\CaseManagement\Services\CaseTicketUtilityServices;
use Simplydigital\CaseManagement\Models\tickets;

class CaseBuildCalcTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_buildCalcData(): void
    {
        $caseId = 2; // Replace with an actual case ID from your database
        $fieldToCheck = 'response_time'; // Replace with the field you want to check
        $ServiceUtility = new CaseTicketUtilityServices();
        $sampleCase = tickets::find($caseId); // Replace with an actual case ID from your database
        // dump($sampleCase);
        $this->assertEquals($caseId, $sampleCase->id); // Replace with the expected case ID

        $calcData = $ServiceUtility->buildCalcData($sampleCase, $fieldToCheck);
        dump($calcData);

        $calc = $ServiceUtility->calcRespondByTime();
        dump($calc);
    }
}
