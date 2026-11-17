<?php

namespace Tests\Unit;

use Carbon\Carbon;
use DomainException;
use InvalidArgumentException;
use Simplydigital\CaseManagement\Services\CasePublicHolidayService;
use Simplydigital\CaseManagement\Services\SLACalculationService;
use Tests\TestCase;

class SLACalculationRegressionTest extends TestCase
{
    private function sla(array $overrides = []): array
    {
        return array_replace([
            'startDate' => Carbon::parse('2026-11-16 09:00:00', 'Europe/London')->timestamp,
            'response_time' => 3600,
            'service_start_time' => 9 * 3600,
            'service_end_time' => 17 * 3600,
            'startDay' => 'monday',
            'endDay' => 'friday',
            'includePublicHoliday' => false,
        ], $overrides);
    }

    public function test_a_deadline_finishes_exactly_at_service_end(): void
    {
        config()->set('app.timezone', 'Europe/London');
        $calc = new SLACalculationService;
        $data = $this->sla([
            'startDate' => Carbon::parse('2026-11-16 16:00:00', 'Europe/London')->timestamp,
        ]);

        $this->assertSame('2026-11-16 17:00:00', $calc->calcRespondByTime($data));
    }

    public function test_a_deadline_crosses_weekend_without_counting_weekend_hours(): void
    {
        config()->set('app.timezone', 'Europe/London');
        $calc = new SLACalculationService;
        $data = $this->sla([
            'startDate' => Carbon::parse('2026-11-13 16:30:00', 'Europe/London')->timestamp,
            'response_time' => 2 * 3600,
        ]);

        $this->assertSame('2026-11-16 10:30:00', $calc->calcRespondByTime($data));
    }

    public function test_time_started_after_service_hours_counts_from_next_working_day(): void
    {
        config()->set('app.timezone', 'Europe/London');
        $calc = new SLACalculationService;
        $data = $this->sla([
            'startDate' => Carbon::parse('2026-11-13 18:15:00', 'Europe/London')->timestamp,
        ]);

        $this->assertSame('2026-11-16 10:00:00', $calc->calcRespondByTime($data));
    }

    public function test_deadline_longer_than_twenty_calendar_days_does_not_silently_truncate(): void
    {
        config()->set('app.timezone', 'Europe/London');
        $calc = new SLACalculationService;
        $data = $this->sla([
            'response_time' => 160 * 3600, // 20 working days
        ]);

        $this->assertSame('2026-12-11 17:00:00', $calc->calcRespondByTime($data));
    }

    public function test_time_precision_preserves_seconds(): void
    {
        config()->set('app.timezone', 'Europe/London');
        $calc = new SLACalculationService;
        $data = $this->sla([
            'startDate' => Carbon::parse('2026-11-16 09:00:13', 'Europe/London')->timestamp,
            'response_time' => 97,
        ]);

        $this->assertSame('2026-11-16 09:01:50', $calc->calcRespondByTime($data));
    }

    public function test_holiday_is_skipped_when_policy_excludes_bank_holidays(): void
    {
        config()->set('app.timezone', 'Europe/London');
        $calc = new SLACalculationService;
        $data = $this->sla([
            'startDate' => Carbon::parse('2026-05-01 16:30:00', 'Europe/London')->timestamp,
            'includePublicHoliday' => true,
        ]);

        $this->assertSame('2026-05-05 09:30:00', $calc->calcRespondByTime($data));
        $data['includePublicHoliday'] = false;
        $this->assertSame('2026-05-04 09:30:00', $calc->calcRespondByTime($data));
    }

    public function test_public_holidays_are_checked_for_year_of_case_not_current_year(): void
    {
        config()->set('app.timezone', 'Europe/London');
        $calc = new SLACalculationService;

        $this->assertTrue($calc->isPublicHoliday(
            Carbon::parse('2030-05-06 12:00:00', 'Europe/London')->timestamp
        ));
        $this->assertFalse($calc->isPublicHoliday(
            Carbon::parse('2030-05-07 12:00:00', 'Europe/London')->timestamp
        ));
    }

    public function test_bank_holidays_follow_first_and_last_monday_rules(): void
    {
        $holidays = (new CasePublicHolidayService)->getBankHolidaysForYear(2026)['dmy'];

        $this->assertContains('04-05-2026', $holidays);
        $this->assertContains('25-05-2026', $holidays);
        $this->assertContains('31-08-2026', $holidays);
        $this->assertNotContains('07-05-2026', $holidays);
    }

    public function test_daylight_saving_does_not_shift_local_business_opening_time(): void
    {
        config()->set('app.timezone', 'Europe/London');
        $calc = new SLACalculationService;
        $data = $this->sla([
            'startDate' => Carbon::parse('2026-03-29 01:30:00', 'Europe/London')->timestamp,
            'startDay' => 'sunday',
            'endDay' => 'sunday',
            'response_time' => 3600,
        ]);

        $this->assertSame('2026-03-29 10:00:00', $calc->calcRespondByTime($data));
    }

    public function test_resuming_on_an_off_day_does_not_change_contractual_working_week(): void
    {
        config()->set('app.timezone', 'Europe/London');
        $calculator = \Mockery::mock(SLACalculationService::class)->makePartial();
        $calculator->shouldReceive('createSlaData')->once()->andReturn(
            $this->sla([
                'startDay' => 'monday',
                'endDay' => 'monday',
                'response_time' => 3600,
            ])
        );

        $start = Carbon::parse('2026-11-17 10:00:00', 'Europe/London'); // Tuesday
        $ticket = new \Simplydigital\CaseManagement\Models\tickets;

        $this->assertSame(
            '2026-11-23 10:00:00', // Next Monday; Tuesday is not an SLA day
            $calculator->calcCustomRespondByTime($ticket, 3600, $start)
        );
    }

    public function test_invalid_or_overnight_working_hours_are_rejected_not_guessed(): void
    {
        $calc = new SLACalculationService;
        $this->expectException(InvalidArgumentException::class);
        $calc->calcRespondByTime($this->sla([
            'service_start_time' => 17 * 3600,
            'service_end_time' => 9 * 3600,
        ]));
    }

    public function test_negative_allowance_is_rejected(): void
    {
        $calc = new SLACalculationService;
        $this->expectException(InvalidArgumentException::class);
        $calc->calcRespondByTime($this->sla(['response_time' => -1]));
    }
}
