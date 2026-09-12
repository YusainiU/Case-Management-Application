<?php

namespace Simplydigital\UserManagement\Console;

use Illuminate\Console\Command;
use Simplydigital\UserManagement\Services\PermissionSynchronizer;

class SyncPermissionsCommand extends Command
{
    protected $signature = 'permissions:sync';

    protected $description = 'Synchronise package permissions with the database';

    public function handle(
        PermissionSynchronizer $synchronizer
    ): int {
        $synchronizer->sync();

        $this->info('Permissions synchronised successfully.');

        return self::SUCCESS;
    }
}