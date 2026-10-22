<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class CustomerPortalLoginTest extends TestCase
{
    public function test_customer_portal_login_uses_its_own_layout_and_shared_fortify_login_endpoint(): void
    {
        $response = $this->get(route('portal.login'));

        $response->assertOk()
            ->assertSee('Support that keeps you in the loop.')
            ->assertSee('Sign in to your portal')
            ->assertSee('name="email"', false)
            ->assertSee(route('login.store'), false)
            ->assertSee(route('login'), false)
            ->assertSessionHas('url.intended', route('portal.dashboard', absolute: false));
    }

    public function test_guest_visiting_the_portal_is_redirected_to_the_portal_login(): void
    {
        $this->get(route('portal.dashboard'))
            ->assertRedirect(route('portal.login'));
    }

    public function test_internal_login_remains_separate_and_links_to_the_customer_portal(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Log in to your account')
            ->assertSee('Sign in to the customer portal')
            ->assertSee(route('portal.login'), false)
            ->assertDontSee('Support that keeps you in the loop.');
    }

    public function test_welcome_page_provides_internal_and_customer_login_entries(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('login.session-reset', ['destination' => 'internal']), false)
            ->assertSee(route('login.session-reset', ['destination' => 'customer']), false)
            ->assertSee('Internal team login')
            ->assertSee('Customer portal login');
    }

    public function test_internal_login_entry_invalidates_existing_authentication_and_session_data(): void
    {
        $this->actingAs(User::factory()->withoutTwoFactor()->make())
            ->withSession([
                'login.id' => 123,
                'previous_login_state' => 'stale',
            ]);

        $response = $this->post(route('login.session-reset', ['destination' => 'internal']));

        $response
            ->assertRedirect(route('login'))
            ->assertSessionMissing('login.id')
            ->assertSessionMissing('previous_login_state');

        $this->assertGuest();
    }

    public function test_customer_login_entry_invalidates_existing_authentication_and_redirects_to_portal_login(): void
    {
        $this->actingAs(User::factory()->withoutTwoFactor()->make())
            ->withSession(['previous_login_state' => 'stale']);

        $response = $this->post(route('login.session-reset', ['destination' => 'customer']));

        $response
            ->assertRedirect(route('portal.login'))
            ->assertSessionMissing('previous_login_state');

        $this->assertGuest();
    }
}
