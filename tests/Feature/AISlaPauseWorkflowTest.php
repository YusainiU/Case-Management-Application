<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Simplydigital\AIManagement\Livewire\CaseAssistant;
use Simplydigital\AIManagement\Livewire\SlaPauseControls;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseSource;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\slaTask;
use Simplydigital\CaseManagement\Models\SlaTaskPause;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseTicketUtilityServices;
use Simplydigital\CaseManagement\Services\SLACalculationService;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AISlaPauseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_assistant_shows_seventh_staff_controlled_sla_stage(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);

        $this->actingAs($viewer);
        Livewire::test(CaseAssistant::class, ['case' => $case])
            ->assertSee('SLA pause / resume')
            ->assertSee('Pause / Resume SLA (staff controlled)')
            ->call('openWorkflow', 'sla-pause')
            ->assertSee('The case has no SLA task')
            ->assertSee('You can view the SLA status');
    }

    public function test_staff_cannot_pause_an_sla_without_a_task(): void
    {
        $staff = User::factory()->create();
        $this->grant($staff, 'case.view', 'case.update');
        $case = $this->caseFor($staff);

        $this->actingAs($staff);
        Livewire::test(SlaPauseControls::class, ['case' => $case])
            ->set('confirmPause', true)
            ->call('pause')
            ->assertSee('no SLA task');

        $this->assertSame(CaseState::Open, $case->refresh()->state);
        $this->assertDatabaseCount('sla_task_pauses', 0);
    }

    public function test_viewer_cannot_pause_or_resume_or_cancel_sla(): void
    {
        $viewer = User::factory()->create();
        $this->grant($viewer, 'case.view');
        $case = $this->caseFor($viewer);

        $this->actingAs($viewer);
        Livewire::test(SlaPauseControls::class, ['case' => $case])
            ->set('confirmPause', true)
            ->call('pause')->assertForbidden();

        Livewire::test(SlaPauseControls::class, ['case' => $case])
            ->call('resume')->assertForbidden();

        Livewire::test(SlaPauseControls::class, ['case' => $case])
            ->call('cancelPause')->assertForbidden();
    }

    public function test_shared_manual_service_pauses_and_resumes_using_saved_remaining_sla(): void
    {
        $staff = User::factory()->create();
        $this->grant($staff, 'case.view', 'case.update');
        $case = $this->caseFor($staff);
        $case->update(['state' => CaseState::In_Progress]);
        $sla = slaTask::query()->create([
            'ticket_id' => $case->id,
            'name' => 'SLA pause test',
            'state' => CaseState::In_Progress,
            'active' => true,
            'respond_by' => now()->addHours(3),
            'fix_by' => now()->addHours(6),
        ]);
        $originalRespondBy = $sla->respond_by->toDateTimeString();

        $calculator = \Mockery::mock(SLACalculationService::class);
        $calculator->shouldReceive('calculatePauseRemainingValue')->once()
            ->andReturn(['response_time_remaining' => 5400, 'fix_time_remaining' => 10800]);
        $calculator->shouldReceive('calcCustomRespondByTime')->twice()
            ->andReturnUsing(fn ($ticket, $seconds, $at) => $at->copy()->addSeconds($seconds));
        $this->app->instance(SLACalculationService::class, $calculator);

        $this->actingAs($staff);
        $service = app(CaseTicketUtilityServices::class);
        $pause = $service->pauseCaseSla($case);
        $this->assertInstanceOf(SlaTaskPause::class, $pause);
        $this->assertSame(5400, (int) $pause->remaining);
        $this->assertSame(10800, (int) $pause->remaining_fix_time);
        $this->assertSame(CaseState::Paused, $case->refresh()->state);
        $this->assertSame(CaseState::Paused, $sla->refresh()->state);
        $this->assertNotNull($sla->paused_at);
        $this->assertTrue($sla->paused);
        $this->assertFalse((bool) $service->pauseCaseSla($case));
        $this->assertSame(1, SlaTaskPause::query()->where('sla_task_id', $sla->id)->count());

        $this->assertTrue((bool) $service->resumeCaseSla($case));
        $this->assertSame(CaseState::In_Progress, $case->refresh()->state);
        $this->assertSame(CaseState::In_Progress, $sla->refresh()->state);
        $this->assertNull($sla->paused_at);
        $this->assertFalse($sla->paused);
        $this->assertNotNull($pause->refresh()->resumed_at);
        $this->assertNotSame($originalRespondBy, $sla->respond_by->toDateTimeString());
        $this->assertFalse((bool) $service->resumeCaseSla($case));
    }

    public function test_repeat_pause_uses_current_deadlines_not_original_case_creation_time(): void
    {
        $staff = User::factory()->create();
        $case = $this->caseFor($staff);
        $sla = slaTask::query()->create([
            'ticket_id' => $case->id,
            'name' => 'Repeat pause allowance test',
            'state' => CaseState::In_Progress,
            'active' => true,
            'respond_by' => now()->addHours(2),
            'fix_by' => now()->addHours(4),
        ]);

        $calculator = \Mockery::mock(SLACalculationService::class)->makePartial();
        $calculator->shouldReceive('workingMinutesBetween')->twice()
            ->andReturn(90, 210);

        $remaining = $calculator->calculatePauseRemainingValue($case, now());

        $this->assertSame(5400, $remaining['response_time_remaining']);
        $this->assertSame(12600, $remaining['fix_time_remaining']);
    }

    public function test_cancelling_pause_retains_original_sla_deadlines(): void
    {
        $staff = User::factory()->create();
        $this->grant($staff, 'case.view', 'case.update');
        $case = $this->caseFor($staff);
        $case->update(['state' => CaseState::Paused]);
        $respondBy = now()->addHours(3)->startOfMinute();
        $fixBy = now()->addHours(6)->startOfMinute();
        $sla = slaTask::query()->create([
            'ticket_id' => $case->id,
            'name' => 'SLA pause cancellation',
            'state' => CaseState::Paused,
            'active' => true,
            'respond_by' => $respondBy,
            'fix_by' => $fixBy,
            'paused_at' => now()->subMinutes(15),
        ]);
        SlaTaskPause::query()->create([
            'sla_task_id' => $sla->id,
            'paused_at' => $sla->paused_at,
            'remaining' => 2400,
            'remaining_fix_time' => 9000,
        ]);

        $this->actingAs($staff);
        $this->assertTrue((bool) app(CaseTicketUtilityServices::class)->cancelPauseSla($case));

        $this->assertSame(CaseState::In_Progress, $case->refresh()->state);
        $this->assertSame(CaseState::In_Progress, $sla->refresh()->state);
        $this->assertNull($sla->paused_at);
        $this->assertSame($respondBy->toDateTimeString(), $sla->respond_by->toDateTimeString());
        $this->assertSame($fixBy->toDateTimeString(), $sla->fix_by->toDateTimeString());
        $this->assertFalse($sla->paused);
    }

    private function caseFor(User $staff): tickets
    {
        return tickets::query()->create([
            'customer_id' => Customer::factory()->create()->id,
            'created_by' => $staff->id,
            'category' => CaseCategory::TechnicalSupport,
            'source' => CaseSource::Phone,
            'state' => CaseState::Open,
            'short_description' => 'SLA workflow integration',
            'description' => 'Example case for pausing an SLA.',
            'contact_telephone' => '0123456789',
            'contact_email' => 'test@example.test',
        ]);
    }

    private function grant(User $user, string ...$permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $user->givePermissionTo($permissions);
    }
}
