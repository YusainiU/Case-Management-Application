<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Exceptions;
use Livewire\Livewire;
use RuntimeException;
use Simplydigital\CaseManagement\Events\PublicHolidayEventProvider;
use Simplydigital\CaseManagement\Services\CasePublicHolidayService;
use Simplydigital\EventPlannerManagement\Contracts\PlannerEventProvider;
use Simplydigital\EventPlannerManagement\DTO\PlannerEvent;
use Simplydigital\EventPlannerManagement\Livewire\Planner;
use Simplydigital\EventPlannerManagement\Services\PlannerEventProviderRegistry;
use Simplydigital\EventPlannerManagement\Services\PlannerService;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PlannerPublicHolidayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.timezone', 'Europe/London');
        config()->set('case-management.public_holidays', [
            'country' => 'GB', 'source' => 'custom', 'subdivision' => null,
            'custom' => ['GB' => [2026 => [
                '2026-10-31' => 'October closure',
                '2026-11-01' => 'November closure',
                '2026-12-01' => 'December closure',
            ]]],
        ]);
    }

    private function staff(): User
    {
        $staff = User::factory()->create();
        Permission::findOrCreate('event.view', 'web');
        $staff->givePermissionTo('event.view');

        return $staff;
    }

    private function useHolidayPlanner(): void
    {
        $planner = new PlannerService(new PlannerEventProviderRegistry);
        $planner->registerProvider(new PublicHolidayEventProvider(new CasePublicHolidayService));
        $planner->registerProvider(new class implements PlannerEventProvider
        {
            public function events(CarbonInterface $start, CarbonInterface $end, ?int $userId = null): Collection
            {
                return collect([new PlannerEvent(
                    id: '17', title: 'Existing case', start: Carbon::parse('2026-11-02 10:00:00'), type: 'case'
                )]);
            }
        });
        $this->app->instance(PlannerService::class, $planner);
    }

    public function test_month_grid_includes_adjacent_month_holidays_as_read_only_all_day_entries(): void
    {
        $this->travelTo(Carbon::parse('2026-11-01 12:00:00'));
        $this->useHolidayPlanner();

        Livewire::actingAs($this->staff())->test(Planner::class)
            ->assertSee('October closure')
            ->assertSee('November closure')
            ->assertSee('December closure')
            ->assertSee('Public holiday · All day')
            ->assertSeeHtml('data-event-type="public_holiday"')
            ->assertDontSeeHtml('wire:click="openEventModal(public-holiday')
            ->assertSeeHtml('wire:click="openEventModal(17)"')
            ->assertSet('showEventModal', false);
    }

    public function test_holiday_labels_are_escaped_and_refresh_uses_the_selected_country(): void
    {
        $this->travelTo(Carbon::parse('2026-11-01 12:00:00'));
        config()->set('case-management.public_holidays.custom.GB.2026', [
            '2026-11-01' => '<script>alert(1)</script>',
        ]);
        $this->useHolidayPlanner();

        $component = Livewire::actingAs($this->staff())->test(Planner::class)
            ->assertSeeHtml('&lt;script&gt;alert(1)&lt;/script&gt;')
            ->assertDontSeeHtml('<script>alert(1)</script>');
        config()->set('case-management.public_holidays.country', 'FR');
        config()->set('case-management.public_holidays.custom.FR.2026', ['2026-11-11' => 'Armistice']);
        $component->refresh()->assertSee('Armistice')->assertDontSee('alert(1)');
    }

    public function test_calendar_failures_show_a_warning_without_hiding_existing_case_events(): void
    {
        Exceptions::fake();
        $this->travelTo(Carbon::parse('2026-11-01 12:00:00'));
        config()->set('case-management.public_holidays.custom.GB', []);
        $this->useHolidayPlanner();

        Livewire::actingAs($this->staff())->test(Planner::class)
            ->assertSee('Existing case')
            ->assertSee('Public holidays could not be fully loaded.')
            ->assertSeeHtml('role="alert"');
        Exceptions::assertReported(RuntimeException::class);
    }

    public function test_users_without_planner_permission_cannot_view_holiday_planner(): void
    {
        $this->travelTo(Carbon::parse('2026-11-01 12:00:00'));
        $this->useHolidayPlanner();

        Livewire::actingAs(User::factory()->create())->test(Planner::class)->assertForbidden();
    }
}
