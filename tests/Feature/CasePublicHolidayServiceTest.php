<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CasePublicHolidayService;
use Simplydigital\CaseManagement\Services\SLACalculationService;
use Simplydigital\CaseManagement\Services\SLALoggerService;
use Tests\TestCase;

class CasePublicHolidayServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('cache.default', 'array');
        config()->set('app.timezone', 'UTC');
        config()->set('case-management.public_holidays', [
            'country' => 'GB',
            'source' => 'auto',
            'subdivision' => null,
            'custom' => [],
        ]);
    }

    private function holiday(string $date, string $country, array $overrides = []): array
    {
        return array_replace([
            'date' => $date,
            'name' => 'Public holiday',
            'countryCode' => $country,
            'nationalHoliday' => true,
            'subdivisionCodes' => null,
            'holidayTypes' => ['Public'],
        ], $overrides);
    }

    public function test_default_calendar_keeps_england_wales_holidays_without_network_access(): void
    {
        Http::preventStrayRequests();
        config()->set('case-management.public_holidays', []);
        $service = new CasePublicHolidayService;

        $this->assertSame('GB', $service->getCountryCode());
        $this->assertSame([
            '2026-01-01', '2026-04-03', '2026-04-06', '2026-05-04',
            '2026-05-25', '2026-08-31', '2026-12-25', '2026-12-28',
        ], $service->getBankHolidaysForYear(2026)['ymd']);
        Http::assertNothingSent();
    }

    public function test_country_code_is_normalized_and_provider_dates_preserve_the_existing_output_format(): void
    {
        Http::preventStrayRequests();
        config()->set('case-management.public_holidays.country', ' fr ');
        Http::fake([
            'https://nagerholidays.com/api/v4/Holidays/FR/2026' => Http::response([
                $this->holiday('2026-07-14', 'FR', ['name' => 'National Day']),
                $this->holiday('2026-05-01', 'FR', ['name' => 'Labour Day']),
            ]),
        ]);
        $result = (new CasePublicHolidayService)->getBankHolidaysForYear(2026);

        $this->assertSame(['2026-05-01', '2026-07-14'], $result['ymd']);
        $this->assertSame(['01-05-2026', '14-07-2026'], $result['dmy']);
        $this->assertSame(['01-05-2026' => 'Labour Day', '14-07-2026' => 'National Day'], $result['description']);
        Http::assertSent(fn (Request $request): bool =>
            $request->url() === 'https://nagerholidays.com/api/v4/Holidays/FR/2026'
            && $request->method() === 'GET'
            && $request->data() === []
        );
    }

    public function test_any_country_code_uses_the_provider_without_a_hard_coded_country_list(): void
    {
        Http::preventStrayRequests();
        config()->set('case-management.public_holidays.country', 'MY');
        Http::fake([
            'https://nagerholidays.com/api/v4/Holidays/MY/2030' => Http::response([
                $this->holiday('2030-08-31', 'MY'),
            ]),
        ]);

        $this->assertSame(['31-08-2030'], (new CasePublicHolidayService)->getBankHolidaysForYear(2030)['dmy']);
        Http::assertSentCount(1);
    }

    public function test_only_public_national_and_selected_subdivision_holidays_are_used_and_cached(): void
    {
        Http::preventStrayRequests();
        config()->set('case-management.public_holidays.country', 'DE');
        Http::fake([
            'https://nagerholidays.com/api/v4/Holidays/DE/2026' => Http::response([
                $this->holiday('2026-01-01', 'DE'),
                $this->holiday('2026-01-06', 'DE', ['nationalHoliday' => false, 'subdivisionCodes' => ['DE-BY']]),
                $this->holiday('2026-03-08', 'DE', ['nationalHoliday' => false, 'subdivisionCodes' => ['DE-BE']]),
                $this->holiday('2026-12-24', 'DE', ['holidayTypes' => ['Observance']]),
            ]),
        ]);
        $service = new CasePublicHolidayService;
        $this->assertSame(['01-01-2026'], $service->getBankHolidaysForYear(2026)['dmy']);
        config()->set('case-management.public_holidays.subdivision', 'DE-BY');
        $this->assertSame(['01-01-2026', '06-01-2026'], $service->getBankHolidaysForYear(2026)['dmy']);
        $this->assertSame(['01-01-2026', '06-01-2026'], (new CasePublicHolidayService)->getBankHolidaysForYear(2026)['dmy']);
        Http::assertSentCount(2);
    }

    public function test_explicit_provider_can_replace_the_legacy_uk_calendar_and_caches_years_separately(): void
    {
        Http::preventStrayRequests();
        config()->set('case-management.public_holidays.source', 'nager');
        Http::fake([
            'https://nagerholidays.com/api/v4/Holidays/GB/2026' => Http::response([
                $this->holiday('2026-01-01', 'GB'),
            ]),
            'https://nagerholidays.com/api/v4/Holidays/GB/2027' => Http::response([
                $this->holiday('2027-01-01', 'GB'),
            ]),
        ]);
        $service = new CasePublicHolidayService;

        $this->assertSame(['01-01-2026'], $service->getBankHolidaysForYear(2026)['dmy']);
        $this->assertSame(['01-01-2027'], $service->getBankHolidaysForYear(2027)['dmy']);
        $this->assertSame(['01-01-2026'], (new CasePublicHolidayService)->getBankHolidaysForYear(2026)['dmy']);
        Http::assertSentCount(2);
    }

    public function test_custom_calendar_supports_any_country_offline_and_changes_invalidate_sla_cache(): void
    {
        Http::preventStrayRequests();
        config()->set('case-management.public_holidays', [
            'country' => 'MY', 'source' => 'custom',
            'custom' => ['MY' => [2026 => ['2026-08-31' => 'National Day']]],
        ]);
        $calculator = new SLACalculationService;
        $date = Carbon::parse('2026-08-31 12:00:00', 'UTC')->timestamp;
        $this->assertTrue($calculator->isPublicHoliday($date));

        config()->set('case-management.public_holidays.custom.MY.2026', []);
        $this->assertFalse($calculator->isPublicHoliday($date));
        Http::assertNothingSent();
    }

    public function test_custom_calendar_requires_an_explicit_entry_for_every_requested_year(): void
    {
        config()->set('case-management.public_holidays.source', 'custom');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No custom public holiday calendar configured for GB/2030.');
        (new CasePublicHolidayService)->getBankHolidaysForYear(2030);
    }

    public function test_invalid_custom_dates_are_rejected_instead_of_normalized(): void
    {
        config()->set('case-management.public_holidays.source', 'custom');
        config()->set('case-management.public_holidays.custom.GB.2026', ['2026-02-30' => 'Invalid']);
        $this->expectException(InvalidArgumentException::class);
        (new CasePublicHolidayService)->getBankHolidaysForYear(2026);
    }

    public function test_invalid_country_code_is_rejected_before_network_access(): void
    {
        Http::preventStrayRequests();
        config()->set('case-management.public_holidays.country', '../FR');
        $this->expectException(InvalidArgumentException::class);
        (new CasePublicHolidayService)->getBankHolidaysForYear(2026);
    }

    public function test_subdivision_must_belong_to_the_selected_country(): void
    {
        config()->set('case-management.public_holidays.subdivision', 'DE-BY');
        $this->expectException(InvalidArgumentException::class);
        (new CasePublicHolidayService)->getBankHolidaysForYear(2026);
    }

    public function test_unknown_source_is_rejected_instead_of_using_a_different_calendar(): void
    {
        config()->set('case-management.public_holidays.source', 'unknown');
        $this->expectException(InvalidArgumentException::class);
        (new CasePublicHolidayService)->getBankHolidaysForYear(2026);
    }

    public function test_provider_errors_are_not_cached_and_never_fall_back_to_uk_dates(): void
    {
        Http::preventStrayRequests();
        config()->set('case-management.public_holidays.country', 'FR');
        Http::fake([
            'https://nagerholidays.com/api/v4/Holidays/FR/2026' => Http::sequence()
                ->push([], 404)
                ->push([$this->holiday('2026-07-14', 'FR')]),
        ]);
        $service = new CasePublicHolidayService;
        try {
            $service->getBankHolidaysForYear(2026);
            $this->fail('A missing country calendar must stop the calculation.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Unable to load public holidays for FR/2026', $exception->getMessage());
        }

        $this->assertSame(['14-07-2026'], $service->getBankHolidaysForYear(2026)['dmy']);
        Http::assertSentCount(2);
    }

    public function test_connection_failure_stops_calculation(): void
    {
        Http::preventStrayRequests();
        config()->set('case-management.public_holidays.country', 'FR');
        Http::fake(['https://nagerholidays.com/api/v4/Holidays/FR/2026' => Http::failedConnection()]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to load public holidays for FR/2026');
        (new CasePublicHolidayService)->getBankHolidaysForYear(2026);
    }

    public function test_empty_or_malformed_provider_data_is_rejected_and_not_cached(): void
    {
        Http::preventStrayRequests();
        config()->set('case-management.public_holidays.country', 'FR');
        Http::fake([
            'https://nagerholidays.com/api/v4/Holidays/FR/2026' => Http::sequence()
                ->push([])
                ->push([$this->holiday('2026-02-30', 'FR')])
                ->push([$this->holiday('2027-01-01', 'FR')])
                ->push([$this->holiday('2026-07-14', 'DE')])
                ->push(['error' => 'bad data'])
                ->push('not json'),
        ]);
        for ($attempt = 0; $attempt < 6; $attempt++) {
            try {
                (new CasePublicHolidayService)->getBankHolidaysForYear(2026);
                $this->fail('Invalid calendar data must not produce an SLA calendar.');
            } catch (RuntimeException $exception) {
                $this->assertStringContainsString('public holiday data for FR/2026', $exception->getMessage());
            }
        }
        Http::assertSentCount(6);
    }

    public function test_specific_date_returns_only_the_matching_holiday_or_an_empty_list(): void
    {
        Http::preventStrayRequests();
        config()->set('case-management.public_holidays.country', 'FR');
        Http::fake([
            'https://nagerholidays.com/api/v4/Holidays/FR/2026' => Http::response([
                $this->holiday('2026-07-14', 'FR', ['name' => 'National Day']),
            ]),
        ]);
        $service = new CasePublicHolidayService;
        $this->assertSame(['14-07-2026'], $service->getPublicHolidays('14-07-2026', false));
        $this->assertSame(['14-07-2026' => 'National Day'], $service->holidayDetails);
        $this->assertSame([], $service->getPublicHolidays('15-07-2026', false));
        $this->assertSame([], $service->holidayDetails);
        Http::assertSentCount(1);
    }

    public function test_switching_country_and_year_does_not_reuse_holidays_or_descriptions(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://nagerholidays.com/api/v4/Holidays/FR/2030' => Http::response([
                $this->holiday('2030-07-14', 'FR', ['name' => 'National Day']),
            ]),
        ]);
        $service = new CasePublicHolidayService;
        $service->getBankHolidaysForYear(2030);
        $calculator = new SLACalculationService;
        $date = Carbon::parse('2030-07-14 12:00:00', 'UTC')->timestamp;
        $this->assertFalse($calculator->isPublicHoliday($date));
        config()->set('case-management.public_holidays.country', 'FR');
        $this->assertTrue($calculator->isPublicHoliday($date));
        $service->getBankHolidaysForYear(2030);
        $service->getBankHolidaysForYear(2030);
        $this->assertArrayNotHasKey('Boxing Day', $service->listByDescription);
        $this->assertSame(['14-07-2030'], $service->listByDescription['National Day'][2030]);
        config()->set('case-management.public_holidays.country', 'GB');
        $this->assertFalse($calculator->isPublicHoliday($date));
        Http::assertSentCount(1);
    }

    public function test_deadline_and_pause_working_time_use_the_selected_calendar(): void
    {
        Http::preventStrayRequests();
        config()->set('app.timezone', 'Europe/Paris');
        config()->set('case-management.public_holidays.country', 'FR');
        Http::fake([
            'https://nagerholidays.com/api/v4/Holidays/FR/2026' => Http::response([
                $this->holiday('2026-07-14', 'FR'),
            ]),
        ]);
        $data = [
            'startDate' => Carbon::parse('2026-07-13 16:30:00', 'Europe/Paris')->timestamp,
            'response_time' => 3600,
            'service_start_time' => 9 * 3600,
            'service_end_time' => 17 * 3600,
            'startDay' => 'monday',
            'endDay' => 'friday',
            'includePublicHoliday' => true,
        ];
        $calculator = \Mockery::mock(SLACalculationService::class, [new SLALoggerService])->makePartial();
        $calculator->shouldReceive('createSlaData')->once()->andReturn($data);

        $this->assertSame('2026-07-15 09:30:00', $calculator->calcRespondByTime($data));
        $this->assertSame(3600, $calculator->workingSecondsBetween(
            new tickets,
            Carbon::parse('2026-07-13 16:30:00', 'Europe/Paris'),
            Carbon::parse('2026-07-15 09:30:00', 'Europe/Paris')
        ));
        $data['includePublicHoliday'] = false;
        $this->assertSame('2026-07-14 09:30:00', $calculator->calcRespondByTime($data));
        $data['includePublicHoliday'] = true;
        config()->set('case-management.public_holidays.country', 'GB');
        $this->assertSame('2026-07-14 09:30:00', $calculator->calcRespondByTime($data));
        Http::assertSentCount(1);
    }

    public function test_sla_without_holiday_exclusion_does_not_need_a_calendar_or_network(): void
    {
        Http::preventStrayRequests();
        config()->set('case-management.public_holidays.country', 'FR');
        $calculator = new SLACalculationService;

        $this->assertTrue($calculator->isBusinessWorkingHours([
            'startDay' => 'monday', 'endDay' => 'friday', 'includePublicHoliday' => false,
        ], Carbon::parse('2026-07-14 12:00:00', 'UTC')));
        Http::assertNothingSent();
    }
}
