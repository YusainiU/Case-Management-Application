<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Simplydigital\CaseManagement\Events\PublicHolidayEventProvider;
use Simplydigital\CaseManagement\Services\CasePublicHolidayService;
use Simplydigital\EventPlannerManagement\Contracts\PlannerEventProvider;
use Simplydigital\EventPlannerManagement\DTO\PlannerEvent;
use Simplydigital\EventPlannerManagement\Services\PlannerEventProviderRegistry;
use Simplydigital\EventPlannerManagement\Services\PlannerService;
use Tests\TestCase;

class PublicHolidayEventProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.timezone', 'Europe/London');
        config()->set('case-management.public_holidays', [
            'country' => 'GB', 'source' => 'custom', 'subdivision' => null,
            'custom' => ['GB' => [2026 => [
                '2026-12-25' => 'Christmas Day',
                '2026-12-28' => 'Boxing Day',
            ]]],
        ]);
    }

    public function test_holidays_are_registered_in_the_application_planner(): void
    {
        $providers = collect(app(PlannerEventProviderRegistry::class)->providers())
            ->filter(fn ($provider): bool => $provider instanceof PublicHolidayEventProvider);
        $this->assertCount(1, $providers);
        $this->assertCount(2, $providers->first()->events(
            Carbon::parse('2026-12-01'), Carbon::parse('2027-01-01')
        ));
    }

    public function test_holidays_are_read_only_all_day_events_using_the_configured_calendar(): void
    {
        $provider = new PublicHolidayEventProvider(new CasePublicHolidayService);
        $events = $provider->events(Carbon::parse('2026-12-25'), Carbon::parse('2026-12-26'), 99);
        $this->assertCount(1, $events);
        $holiday = $events->first();
        $this->assertSame('public-holiday-GB-2026-12-25', $holiday->id);
        $this->assertSame('Christmas Day', $holiday->title);
        $this->assertSame('public_holiday', $holiday->type);
        $this->assertTrue($holiday->allDay);
        $this->assertNull($holiday->url);
        $this->assertSame('2026-12-25T00:00:00+00:00', $holiday->start->toIso8601String());
        $this->assertSame('2026-12-26T00:00:00+00:00', $holiday->end->toIso8601String());
        $this->assertSame('#FEF3C7', $holiday->toArray()['colour']);
        $this->assertSame([], $provider->warnings());

        config()->set('case-management.public_holidays.country', 'FR');
        config()->set('case-management.public_holidays.custom.FR.2026', ['2026-12-26' => 'Regional holiday']);
        $events = $provider->events(Carbon::parse('2026-12-25'), Carbon::parse('2026-12-27'));
        $this->assertSame(['Regional holiday'], $events->pluck('title')->all());
        $this->assertSame('public-holiday-FR-2026-12-26', $events->first()->id);
    }

    public function test_range_end_is_exclusive_and_only_overlapping_holiday_days_are_returned(): void
    {
        $provider = new PublicHolidayEventProvider(new CasePublicHolidayService);
        $start = Carbon::parse('2026-12-25 12:00:00', 'Europe/London');
        $end = Carbon::parse('2026-12-28 00:00:00', 'Europe/London');
        $this->assertSame(['Christmas Day'], $provider->events($start, $end)->pluck('title')->all());
        $this->assertSame('2026-12-25 12:00:00', $start->format('Y-m-d H:i:s'));
        $this->assertSame('2026-12-28 00:00:00', $end->format('Y-m-d H:i:s'));
    }

    public function test_cross_year_ranges_load_both_years_without_loading_the_exclusive_end_year(): void
    {
        config()->set('case-management.public_holidays.custom.GB.2027', ['2027-01-01' => 'New Year']);
        $provider = new PublicHolidayEventProvider(new CasePublicHolidayService);
        $events = $provider->events(CarbonImmutable::parse('2026-12-28'), CarbonImmutable::parse('2027-01-02'));
        $this->assertSame(['Boxing Day', 'New Year'], $events->pluck('title')->all());
        $this->assertSame([], $provider->warnings());
        $this->assertCount(1, $provider->events(Carbon::parse('2026-12-28'), Carbon::parse('2027-01-01')));
        $this->assertSame([], $provider->warnings());
    }

    public function test_all_day_dates_use_application_timezone_and_preserve_local_midnight_across_dst(): void
    {
        config()->set('app.timezone', 'Europe/Paris');
        config()->set('case-management.public_holidays.custom.GB.2026', ['2026-03-29' => 'Local closure']);
        $events = (new PublicHolidayEventProvider(new CasePublicHolidayService))->events(
            Carbon::parse('2026-03-28T23:00:00Z'), Carbon::parse('2026-03-29T22:00:00Z')
        );
        $this->assertCount(1, $events);
        $this->assertSame('2026-03-29T00:00:00+01:00', $events->first()->start->toIso8601String());
        $this->assertSame('2026-03-30T00:00:00+02:00', $events->first()->end->toIso8601String());
    }

    public function test_empty_and_reversed_ranges_do_not_request_a_calendar(): void
    {
        Http::preventStrayRequests();
        config()->set('case-management.public_holidays.country', 'FR');
        config()->set('case-management.public_holidays.source', 'nager');
        $provider = new PublicHolidayEventProvider(new CasePublicHolidayService);
        $this->assertCount(0, $provider->events(Carbon::parse('2026-12-25'), Carbon::parse('2026-12-25')));
        $this->assertCount(0, $provider->events(Carbon::parse('2026-12-26'), Carbon::parse('2026-12-25')));
        $this->assertSame([], $provider->warnings());
        Http::assertNothingSent();
    }

    public function test_calendar_failure_keeps_other_events_and_reports_a_warning_that_clears_on_recovery(): void
    {
        Exceptions::fake();
        config()->set('case-management.public_holidays.custom.GB', []);
        $service = new PlannerService(new PlannerEventProviderRegistry);
        $service->registerProvider(new PublicHolidayEventProvider(new CasePublicHolidayService));
        $service->registerProvider(new class implements PlannerEventProvider
        {
            public function events(CarbonInterface $start, CarbonInterface $end, ?int $userId = null): Collection
            {
                return collect([new PlannerEvent(id: '1', title: 'Existing case', start: $start, type: 'case')]);
            }
        });
        $events = $service->events(Carbon::parse('2026-12-01'), Carbon::parse('2027-01-01'));
        $this->assertSame(['Existing case'], $events->pluck('title')->all());
        $this->assertSame([
            'Public holidays could not be fully loaded. Check the holiday calendar settings or try again later.',
        ], $service->warnings());
        Exceptions::assertReported(RuntimeException::class);

        config()->set('case-management.public_holidays.custom.GB.2026', ['2026-12-25' => 'Christmas Day']);
        $this->assertCount(2, $service->events(Carbon::parse('2026-12-01'), Carbon::parse('2027-01-01')));
        $this->assertSame([], $service->warnings());
    }

    public function test_provider_uses_remote_country_and_subdivision_settings_without_additional_requests(): void
    {
        Http::preventStrayRequests();
        config()->set('cache.default', 'array');
        config()->set('case-management.public_holidays', ['country' => 'DE', 'source' => 'nager', 'subdivision' => 'DE-BY']);
        Http::fake([
            'https://nagerholidays.com/api/v4/Holidays/DE/2026' => Http::response([
                ['date' => '2026-01-01', 'name' => 'New Year', 'countryCode' => 'DE',
                    'nationalHoliday' => true, 'subdivisionCodes' => null, 'holidayTypes' => ['Public']],
                ['date' => '2026-01-06', 'name' => 'Epiphany', 'countryCode' => 'DE',
                    'nationalHoliday' => false, 'subdivisionCodes' => ['DE-BY'], 'holidayTypes' => ['Public']],
            ]),
        ]);
        $provider = new PublicHolidayEventProvider(new CasePublicHolidayService);
        $this->assertSame(['New Year', 'Epiphany'], $provider->events(
            Carbon::parse('2026-01-01'), Carbon::parse('2026-02-01')
        )->pluck('title')->all());
        $this->assertCount(2, $provider->events(Carbon::parse('2026-01-01'), Carbon::parse('2026-02-01')));
        Http::assertSentCount(1);
    }
}
