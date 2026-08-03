<?php

namespace SimplyDigital\NotificationManagement;

use Illuminate\Support\ServiceProvider;

class NotificationManagementServiceProvider extends ServiceProvider
{
        public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}