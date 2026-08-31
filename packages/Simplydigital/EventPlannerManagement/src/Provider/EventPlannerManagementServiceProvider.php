<?php

namespace Simplydigital\EventPlannerManagement\Provider;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Simplydigital\EventPlannerManagement\Contracts\PlannerEventRepository;
use Simplydigital\EventPlannerManagement\Livewire\Planner;
use Simplydigital\EventPlannerManagement\Repositories\PlannerEventRepository as EloquentPlannerEventRepository;
use Simplydigital\EventPlannerManagement\Services\PlannerEventProviderRegistry;
use Simplydigital\EventPlannerManagement\Services\PlannerService;
use Simplydigital\EventPlannerManagement\Middleware\RestrictInternalUserOnly;
use Illuminate\Support\Facades\Route;

class EventPlannerManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/planner.php', 'planner');
        $this->app->singleton('event-planner', function ($app) {
            return new PlannerService($app);
        });
        $this->app->singleton(PlannerEventProviderRegistry::class, fn () => new PlannerEventProviderRegistry);
        $this->app->singleton(PlannerEventRepository::class, fn () => new EloquentPlannerEventRepository);
    }

    public function boot(): void
    {

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'eventplanner');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->publishes([
            __DIR__.'/../Config/planner.php' => config_path('planner.php'),
        ], 'eventplanner-config');

        Livewire::component('planner', Planner::class);

        Route::middleware('web')->group(__DIR__.'/../Routes/web.php');
        Route::aliasMiddleware('restrict.internal.user', RestrictInternalUserOnly::class);         
    }
}
