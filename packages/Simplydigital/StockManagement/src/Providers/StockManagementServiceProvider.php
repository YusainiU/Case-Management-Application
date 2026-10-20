<?php

namespace Simplydigital\StockManagement\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Simplydigital\StockManagement\Authorization\StockManagementPermissionProvider;
use Simplydigital\StockManagement\Livewire\StockRequestCase;
use Simplydigital\StockManagement\Livewire\StockRequestQueue;
use Simplydigital\StockManagement\Models\StockRequest;
use Simplydigital\StockManagement\Policies\StockRequestPolicy;
use Simplydigital\UserManagement\Services\PermissionRegistry;

class StockManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->afterResolving(
            PermissionRegistry::class,
            function (PermissionRegistry $registry): void {
                $registry->registerProvider(new StockManagementPermissionProvider);
            },
        );
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'stockmanagement');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        Route::middleware('web')->group(__DIR__.'/../Routes/web.php');

        Livewire::component('stock-request-case', StockRequestCase::class);
        Livewire::component('stock-request-queue', StockRequestQueue::class);

        Gate::policy(StockRequest::class, StockRequestPolicy::class);
    }
}
