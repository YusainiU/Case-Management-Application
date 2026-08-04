<?php

namespace SimplyDigital\NotificationManagement;

use Illuminate\Support\ServiceProvider;
use Simplydigital\NotificationManagement\Services\NotificationService;

class NotificationManagementServiceProvider extends ServiceProvider
{

    private array $services = [
        'notification-services' => NotificationService::class,
    ];  
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load views with notification namespace
        $this->loadViewsFrom( __DIR__ . '/Notification/templates', 'notification');

        $this->registerServices();

    }

    public function registerServices()
    {
        foreach ($this->services as $key => $service) {
            $this->app->singleton($key, fn () => new $service());
        }        
    }    
}