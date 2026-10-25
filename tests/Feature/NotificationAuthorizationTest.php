<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Simplydigital\NotificationManagement\Livewire\Admin\QueuedMailJobs;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class NotificationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_view_permission_can_access_queued_mail_jobs_without_update_permission(): void
    {
        $user = User::factory()->create();
        $this->registerNotificationPermissions();
        $user->givePermissionTo('notification.view');

        $response = $this->actingAs($user)->get(route('notifcation.jobs.view'));

        $this->assertSame('package_project_test', DB::connection()->getDatabaseName());
        $response->assertOk();
    }

    public function test_user_without_view_permission_cannot_access_queued_mail_jobs(): void
    {
        $user = User::factory()->create();
        $this->registerNotificationPermissions();

        $response = $this->actingAs($user)->get(route('notifcation.jobs.view'));

        $response->assertForbidden();
    }

    public function test_user_with_view_permission_cannot_run_queued_job_without_update_permission(): void
    {
        $user = User::factory()->create();
        $this->registerNotificationPermissions();
        $user->givePermissionTo('notification.view');
        $this->actingAs($user);

        Livewire::test(QueuedMailJobs::class)
            ->call('runNow', 1)
            ->assertForbidden();
    }

    private function registerNotificationPermissions(): void
    {
        Permission::findOrCreate('notification.view', 'web');
        Permission::findOrCreate('notification.update', 'web');
    }
}
