<?php

namespace Simplydigital\EventPlannerManagement\Provider;

use Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Simplydigital\FileUploadManagement\Models\UploadedFile;
use Simplydigital\UserManagement\Services\PermissionRegistry;
use Simplydigital\EventPlannerManagement\Authorization\EventManagementPermissionProvider;
use Simplydigital\EventPlannerManagement\Policies\EventPolicy;
use Livewire\Livewire;
use Simplydigital\EventPlannerManagement\Contracts\PlannerEventRepository;
use Simplydigital\EventPlannerManagement\Livewire\Planner;
use Simplydigital\EventPlannerManagement\Middleware\RestrictInternalUserOnly;
use Simplydigital\EventPlannerManagement\Models\PlannerEvent;
use Simplydigital\EventPlannerManagement\Repositories\PlannerEventRepository as EloquentPlannerEventRepository;
use Simplydigital\EventPlannerManagement\Services\PlannerEventProviderRegistry;
use Simplydigital\EventPlannerManagement\Services\PlannerService;

class EventPlannerManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/planner.php', 'planner');
        $this->app->singleton(PlannerEventProviderRegistry::class, fn () => new PlannerEventProviderRegistry);
        $this->app->singleton(PlannerEventRepository::class, fn () => new EloquentPlannerEventRepository);
        $this->app->singleton(PlannerService::class);
        $this->registerUserPermissions();

        $this->publishes([
            __DIR__.'/../Resources/js' => resource_path('vendor/eventplanner'),
        ], 'eventplanner-assets');
    }

    public function registerUserPermissions()
    {
        $this->app->afterResolving(
            PermissionRegistry::class,
            function (PermissionRegistry $registry) {
                $registry->registerProvider(
                    new EventManagementPermissionProvider
                );
            }
        );

    }      

    public function boot(): void
    {

        $this->loadPolicies();
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'eventplanner');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->publishes([
            __DIR__.'/../Config/planner.php' => config_path('planner.php'),
        ], 'eventplanner-config');

        Livewire::component('planner', Planner::class);

        Route::middleware('web')->group(__DIR__.'/../Routes/web.php');
        Route::aliasMiddleware('restrict.internal.user', RestrictInternalUserOnly::class);
    }
    
    public function loadPolicies()
    {
        Gate::policy(UploadedFile::class, EventPolicy::class);
        Gate::policy(PlannerEvent::class, EventPolicy::class);
    }     

}
