<?php

namespace Simplydigital\NotificationManagement;

use Illuminate\Support\ServiceProvider;
use Simplydigital\NotificationManagement\Notifications\Services\NotificationService;
use Livewire\Livewire;
use Simplydigital\NotificationManagement\Livewire\Admin\QueuedMailJobs;
use Simplydigital\NotificationManagement\Middleware\RestrictInternalUserOnly;
use Illuminate\Support\Facades\Route;

//use Simplydigital\NotificationManagement\Notifications\Services\RegisterEmailListenerService;


class NotificationManagementServiceProvider extends ServiceProvider
{
    private array $services = [
        'notification-services' => NotificationService::class,
    ];

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load views with notification namespace
        $this->loadViewsFrom(
           __DIR__.'/Notifications/Templates', 
           'notification'
        );

        $this->loadViewsFrom(
            __DIR__.'/Resources/views',
            'notification-management'
        );

        Livewire::component(
            'notification-management.admin.queued-mail-jobs',
            QueuedMailJobs::class
        );        

        $this->registerServices();

        $this->loadRoutes();

        //Email logging diagnostic
        //RegisterEmailListenerService::registerListener();

    }

    public function registerServices()
    {
        foreach ($this->services as $key => $service) {
            $this->app->singleton($key, fn () => new $service);
        }
    }

    public function loadRoutes()
    {        
        Route::middleware('web')->group(__DIR__.'/Routes/web.php');
        Route::aliasMiddleware('restrict.internal.user', RestrictInternalUserOnly::class);   
    }    



}
