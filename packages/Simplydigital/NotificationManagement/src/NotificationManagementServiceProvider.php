<?php

namespace Simplydigital\NotificationManagement;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Simplydigital\NotificationManagement\Authorization\NotificationManagementPermissionProvider;
use Simplydigital\NotificationManagement\Policies\NotificationPolicy;
use Simplydigital\UserManagement\Services\PermissionRegistry;
use Simplydigital\NotificationManagement\Notifications\Services\NotificationService;
use Livewire\Livewire;
use Simplydigital\NotificationManagement\Livewire\Admin\QueuedMailJobs;
use Simplydigital\NotificationManagement\Middleware\RestrictInternalUserOnly;
use Simplydigital\NotificationManagement\Models\Notification;
use Illuminate\Support\Facades\Route;




class NotificationManagementServiceProvider extends ServiceProvider
{
    
    public function register(): void
    {
        $this->app->singleton(
            NotificationService::class,
            fn ($app) => new NotificationService()
        );

        $this->registerUserPermissions();

    }    

    public function boot(): void
    {
        
        $this->loadMigrations();

        $this->loadComponents();       

        $this->loadViews();

        $this->loadRoutes();

        $this->loadPolicies();

        //Email logging diagnostic
        //RegisterEmailListenerService::registerListener();

    }

    public function registerUserPermissions()
    {
        $this->app->afterResolving(
            PermissionRegistry::class,
            function (PermissionRegistry $registry) {
                $registry->registerProvider(
                    new NotificationManagementPermissionProvider
                );
            }
        );

    }
    
    public function loadPolicies()
    {
        Gate::policy(Notification::class, NotificationPolicy::class);
    }    

    public function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function loadComponents()
    {
        Livewire::component(
            'notification-management.admin.queued-mail-jobs',
            QueuedMailJobs::class
        ); 
    }

    public function loadViews()
    {
        // Load views with notification namespace
        $this->loadViewsFrom(
           __DIR__.'/Notifications/Templates', 
           'notification'
        );

        $this->loadViewsFrom(
            __DIR__.'/Resources/views',
            'notification-management'
        );
    }

    public function loadRoutes()
    {        
        Route::middleware('web')->group(__DIR__.'/Routes/web.php');
        Route::aliasMiddleware('restrict.internal.user', RestrictInternalUserOnly::class);   
    }    



}
