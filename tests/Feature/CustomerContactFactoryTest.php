<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Simplydigital\CaseManagement\Enums\CustomerContactRoles;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\CustomerContacts;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerContactFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_assigns_customer_role_to_an_unassigned_user_and_enables_portal_access(): void
    {
        Role::create(['name' => 'Internal User', 'guard_name' => 'web']);
        Role::create(['name' => 'Customer', 'guard_name' => 'web']);

        $assignedUser = User::factory()->create();
        $assignedUser->assignRole('Internal User');
        $unassignedUser = User::factory()->create();
        $customer = Customer::factory()->create();

        $customerContact = CustomerContacts::factory()->create();

        $this->assertModelExists($customerContact);
        $this->assertSame($unassignedUser->id, $customerContact->user_id);
        $this->assertSame($customer->id, $customerContact->customer_id);
        $this->assertTrue($customerContact->has_portal_access);
        $this->assertTrue($unassignedUser->fresh()->hasRole('Customer'));
        $this->assertTrue($assignedUser->fresh()->hasRole('Internal User'));
        $this->assertContains(
            $customerContact->customerRole->value,
            array_map(
                static fn (CustomerContactRoles $role): string => $role->value,
                CustomerContactRoles::cases(),
            ),
        );
        $this->assertNotEmpty($customerContact->description);
    }
}
