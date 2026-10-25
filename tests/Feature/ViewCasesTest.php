<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseSource;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Livewire\Case\ViewCases;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\CaseManagement\Models\tickets;
use Tests\TestCase;

class ViewCasesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_switch_to_workflow_and_view_case_progress_and_details(): void
    {
        Gate::before(
            static fn (?Authenticatable $user, string $ability, array $arguments): bool => true
        );

        $customer = Customer::factory()->create();
        $creator = User::factory()->create();
        $agent = User::factory()->create();
        $case = tickets::query()->create([
            'customer_id' => $customer->id,
            'category' => CaseCategory::Failure->value,
            'created_by' => $creator->id,
            'source' => CaseSource::Portal->value,
            'short_description' => 'Cooling failure',
            'description' => 'The cooling system needs attention.',
            'state' => CaseState::In_Progress->value,
            'assigned_to' => $agent->id,
            'contact_telephone' => '',
            'contact_email' => $creator->email,
        ]);

        Livewire::withQueryParams(['dashboardFilter' => CaseState::Open->value])
            ->test(ViewCases::class)
            ->assertSee('Case Records')
            ->assertSee('Workflow view')
            ->call('toggleViewMode')
            ->assertSee('Case workflow')
            ->assertSee($case->number)
            ->assertSee('Cooling failure')
            ->assertSee($agent->name)
            ->assertSee('Not set')
            ->assertSee('2 of 4 steps complete')
            ->assertSee('wire:click="openProfileModal('.$case->id.')"', false)
            ->call('toggleViewMode')
            ->assertSee('Case Records');
    }

    public function test_unanswered_external_comments_are_indicated_until_an_internal_response_is_added(): void
    {
        Gate::before(
            static fn (?Authenticatable $user, string $ability, array $arguments): bool => true
        );

        $customer = Customer::factory()->create();
        $creator = User::factory()->create();
        $case = tickets::query()->create([
            'customer_id' => $customer->id,
            'category' => CaseCategory::Failure->value,
            'created_by' => $creator->id,
            'source' => CaseSource::Portal->value,
            'short_description' => 'Cooling failure',
            'description' => 'The cooling system needs attention.',
            'state' => CaseState::In_Progress->value,
            'contact_telephone' => '',
            'contact_email' => $creator->email,
        ]);
        $externalComment = TaskLogs::query()->create([
            'name' => $case->number,
            'type' => 'external_update_log',
            'source' => 'external',
            'external_user' => 'Customer contact',
            'ticket_id' => $case->id,
            'description' => 'The issue is still happening.',
        ]);

        $component = Livewire::withQueryParams(['dashboardFilter' => CaseState::Open->value])
            ->test(ViewCases::class)
            ->assertSee('External comment awaiting response')
            ->call('toggleViewMode')
            ->assertSee('External comment awaiting response');

        TaskLogs::query()->create([
            'name' => $case->number,
            'type' => 'internal_update_log',
            'source' => 'internal',
            'ticket_id' => $case->id,
            'description' => 'Responded to the customer.',
            'response_to_internal_comment' => $externalComment->id,
        ]);

        $component
            ->call('$refresh')
            ->assertDontSee('External comment awaiting response')
            ->call('toggleViewMode')
            ->assertDontSee('External comment awaiting response');
    }
}
