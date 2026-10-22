<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseSource;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Livewire\Portal\AddCaseComment;
use Simplydigital\CaseManagement\Livewire\Portal\CreateCase;
use Simplydigital\CaseManagement\Models\Asset;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\CustomerContacts;
use Simplydigital\CaseManagement\Models\serviceLevelAgreements;
use Simplydigital\CaseManagement\Models\slaApplication;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseTicketServices;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_customer_portal(): void
    {
        $this->get(route('portal.dashboard'))
            ->assertRedirect(route('login'));
    }

    #[DataProvider('invalidPortalMemberships')]
    public function test_portal_access_requires_an_active_contact_and_enabled_customer(
        bool $contactActive,
        bool $portalAccess,
        bool $customerActive,
        bool $customerPortalEnabled,
    ): void {
        $customer = Customer::factory()->create([
            'active' => $customerActive,
            'portal_enabled' => $customerPortalEnabled,
        ]);
        $user = User::factory()->create();

        CustomerContacts::query()->create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'active' => $contactActive,
            'has_portal_access' => $portalAccess,
        ]);

        $this->actingAs($user)
            ->get(route('portal.dashboard'))
            ->assertForbidden();
    }

    public static function invalidPortalMemberships(): array
    {
        return [
            'inactive contact' => [false, true, true, true],
            'revoked portal access' => [true, false, true, true],
            'inactive customer' => [true, true, false, true],
            'disabled customer portal' => [true, true, true, false],
        ];
    }

    public function test_customer_contact_can_view_organisation_cases_but_not_another_customers_case(): void
    {
        $customer = $this->createPortalCustomer();
        $otherCustomer = $this->createPortalCustomer();
        $user = $this->createPortalUser($customer);
        $caseCreator = $this->createPortalUser($customer);
        $customerCase = $this->createCase($customer, $caseCreator);
        $otherCase = $this->createCase($otherCustomer, User::factory()->create());

        $this->actingAs($user)
            ->get(route('portal.cases.show', ['case' => $customerCase->id]))
            ->assertOk()
            ->assertSee($customerCase->number)
            ->assertSee($customerCase->short_description);

        $this->get(route('portal.cases.show', ['case' => $otherCase->id]))
            ->assertNotFound();
    }

    public function test_case_page_only_displays_external_customer_updates(): void
    {
        $customer = $this->createPortalCustomer();
        $user = $this->createPortalUser($customer);
        $case = $this->createCase($customer, $user);

        TaskLogs::query()->create([
            'name' => $case->number,
            'type' => config('case-management.logs.type.external_update'),
            'source' => config('case-management.logs.source.external'),
            'user_id' => $user->id,
            'external_user' => $user->name,
            'ticket_id' => $case->id,
            'description' => 'Customer-visible response <script>alert(1)</script>',
        ]);
        TaskLogs::query()->create([
            'name' => $case->number,
            'type' => config('case-management.logs.type.internal_update'),
            'source' => config('case-management.logs.source.internal'),
            'user_id' => $user->id,
            'ticket_id' => $case->id,
            'description' => 'Internal-only note',
        ]);

        $this->actingAs($user)
            ->get(route('portal.cases.show', ['case' => $case->id]))
            ->assertOk()
            ->assertSee('Customer-visible response <script>alert(1)</script>')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('Internal-only note');
    }

    public function test_customer_can_add_reply_to_open_case(): void
    {
        $customer = $this->createPortalCustomer();
        $user = $this->createPortalUser($customer);
        $case = $this->createCase($customer, $user);

        $this->actingAs($user);

        Livewire::test(AddCaseComment::class, ['caseId' => $case->id])
            ->set('comment', 'Please let me know when this is resolved.')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('task_logs', [
            'ticket_id' => $case->id,
            'user_id' => $user->id,
            'source' => 'external',
            'type' => 'external_update_log',
            'description' => 'Please let me know when this is resolved.',
        ]);
    }

    public function test_case_creation_uses_the_existing_ticket_workflow_and_authenticated_contact(): void
    {
        $customer = $this->createPortalCustomer();
        $user = $this->createPortalUser($customer);
        $agreement = serviceLevelAgreements::query()->create([
            'name' => 'Standard support',
            'short_description' => 'Standard support coverage',
            'start_day' => 'monday',
            'end_day' => 'friday',
            'service_start_time' => 28800,
            'service_end_time' => 61200,
            'type' => 'support',
            'response_time' => 1,
            'fixed_time' => 4,
        ]);
        slaApplication::query()->create([
            'name' => 'Customer support',
            'customer_id' => $customer->id,
            'service_level_agreement_id' => $agreement->id,
            'priority' => 2,
            'active' => true,
        ]);
        $asset = Asset::query()->create([
            'customer_id' => $customer->id,
            'short_description' => 'Production server',
            'active' => true,
        ]);

        $ticket = new tickets;
        $ticket->setAttribute('id', 123);

        $this->mock(CaseTicketServices::class)
            ->shouldReceive('createNewTicket')
            ->once()
            ->with(Mockery::on(function (array $data) use ($asset, $customer, $user): bool {
                $this->assertSame($customer->id, $data['customer_id']);
                $this->assertSame([$asset->id], $data['list_of_assets']);
                $this->assertSame($user->id, $data['raised_by_user']);
                $this->assertSame(CaseSource::Portal->value, $data['source']);
                $this->assertSame('Technical Support', $data['category']);
                $this->assertSame('Cannot access the service', $data['short_description']);
                $this->assertSame('The service is unavailable.', $data['description']);
                $this->assertSame($user->email, $data['contact_email']);
                $this->assertArrayNotHasKey('assigned_to', $data);
                $this->assertArrayNotHasKey('state', $data);

                return true;
            }))
            ->andReturn($ticket);

        $this->actingAs($user);

        Livewire::test(CreateCase::class)
            ->set('customerId', (string) $customer->id)
            ->assertSet('customerId', $customer->id)
            ->assertSee('Production server')
            ->set('assetIds', [(string) $asset->id])
            ->set('category', CaseCategory::TechnicalSupport->value)
            ->set('subject', 'Cannot access the service')
            ->set('description', 'The service is unavailable.')
            ->call('submit')
            ->assertRedirect(route('portal.cases.show', ['case' => $ticket->id]));
    }

    public function test_customer_must_select_at_least_one_asset_to_create_a_case(): void
    {
        $customer = $this->createPortalCustomer();
        $user = $this->createPortalUser($customer);

        $this->actingAs($user);

        Livewire::test(CreateCase::class)
            ->set('customerId', (string) $customer->id)
            ->set('category', CaseCategory::TechnicalSupport->value)
            ->set('subject', 'Cannot access the service')
            ->set('description', 'The service is unavailable.')
            ->call('submit')
            ->assertHasErrors(['assetIds' => 'required']);
    }

    public function test_customer_cannot_attach_an_asset_from_another_organisation(): void
    {
        $customer = $this->createPortalCustomer();
        $otherCustomer = $this->createPortalCustomer();
        $user = $this->createPortalUser($customer);
        $otherAsset = Asset::query()->create([
            'customer_id' => $otherCustomer->id,
            'short_description' => 'Other organisation asset',
            'active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(CreateCase::class)
            ->set('customerId', (string) $customer->id)
            ->set('assetIds', [$otherAsset->id])
            ->set('category', CaseCategory::TechnicalSupport->value)
            ->set('subject', 'Cannot access the service')
            ->set('description', 'The service is unavailable.')
            ->call('submit')
            ->assertHasErrors('assetIds.0');
    }

    public function test_customer_cannot_reply_to_closed_case(): void
    {
        $customer = $this->createPortalCustomer();
        $user = $this->createPortalUser($customer);
        $case = $this->createCase($customer, $user, CaseState::Closed);

        $this->actingAs($user);

        Livewire::test(AddCaseComment::class, ['caseId' => $case->id])
            ->set('comment', 'This reply must not be saved.')
            ->call('submit')
            ->assertStatus(403);

        $this->assertDatabaseMissing('task_logs', [
            'ticket_id' => $case->id,
            'description' => 'This reply must not be saved.',
        ]);
    }

    public function test_customer_cannot_reply_after_case_closed_datetime_is_set(): void
    {
        $customer = $this->createPortalCustomer();
        $user = $this->createPortalUser($customer);
        $case = $this->createCase($customer, $user, closedAt: now()->timestamp);

        $this->actingAs($user);

        Livewire::test(AddCaseComment::class, ['caseId' => $case->id])
            ->set('comment', 'This reply must not be saved.')
            ->call('submit')
            ->assertStatus(403);

        $this->assertDatabaseMissing('task_logs', [
            'ticket_id' => $case->id,
            'description' => 'This reply must not be saved.',
        ]);
    }

    private function createPortalCustomer(): Customer
    {
        return Customer::factory()->create([
            'active' => true,
            'portal_enabled' => true,
        ]);
    }

    private function createPortalUser(Customer $customer): User
    {
        $user = User::factory()->create();

        CustomerContacts::query()->create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'active' => true,
            'has_portal_access' => true,
        ]);

        return $user;
    }

    private function createCase(
        Customer $customer,
        User $user,
        CaseState $state = CaseState::Open,
        ?int $closedAt = null,
    ): tickets {
        return tickets::query()->create([
            'customer_id' => $customer->id,
            'category' => CaseCategory::Failure->value,
            'created_by' => $user->id,
            'source' => CaseSource::Portal->value,
            'short_description' => 'Portal case summary',
            'description' => 'Portal case details.',
            'state' => $state->value,
            'closed_datetime' => $closedAt,
            'raised_by_user' => $user->id,
            'contact_telephone' => '',
            'contact_email' => $user->email,
        ]);
    }
}
