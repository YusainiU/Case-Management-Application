<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Simplydigital\CaseManagement\Services\SLACalculationService;
use Tests\TestCase;

class CaseBuildCalcTest extends TestCase
{
    /**
     * Exercise the calculator without assuming a particular case ID exists
     * in the test database. More comprehensive edge cases live in
     * SLACalculationRegressionTest.
     */
    public function test_buildCalcData(): void
    {
        config()->set('app.timezone', 'Europe/London');

        $calculator = new SLACalculationService;
        $data = [
            'startDate' => Carbon::parse('2026-11-17 10:00:00', 'Europe/London')->timestamp,
            'response_time' => 5400, // 90 minutes
            'service_start_time' => 9 * 3600,
            'service_end_time' => 17 * 3600,
            'startDay' => 'monday',
            'endDay' => 'friday',
            'includePublicHoliday' => false,
        ];

        $this->assertSame(
            '2026-11-17 11:30:00',
            $calculator->calcRespondByTime($data)
        );
    }
}
