<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_users_also_creates_a_fake_users_details_record_for_each_user(): void
    {
        $users = User::factory()->count(3)->create();

        $this->assertDatabaseCount('users_details', 3);

        foreach ($users as $user) {
            $this->assertDatabaseHas('users_details', [
                'user_id' => $user->id,
            ]);
        }
    }
}
