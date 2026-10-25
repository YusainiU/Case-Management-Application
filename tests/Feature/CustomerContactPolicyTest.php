<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Gate;
use Simplydigital\CaseManagement\Models\CustomerContacts;
use Simplydigital\CaseManagement\Policies\CustomerContactPolicy;
use Tests\TestCase;

class CustomerContactPolicyTest extends TestCase
{
    public function test_customer_contact_policy_is_registered_for_the_model(): void
    {
        $policy = Gate::getPolicyFor(CustomerContacts::class);

        $this->assertInstanceOf(CustomerContactPolicy::class, $policy);
    }
}
