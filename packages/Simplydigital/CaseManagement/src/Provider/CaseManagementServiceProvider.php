<?php

namespace Simplydigital\CaseManagement\Provider;

use Illuminate\Support\ServiceProvider;

class CaseManagementServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->loadViews();
        $this->loadRoutes();
        $this->loadMigrations();
        $this->publishConfig();
        $this->publishResources();
        $this->registerBladeComponents();
        $this->registerLivewireComponents();
    }

    public function loadViews()
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'casemanagement');
        $this->loadViewComponentsAs('casemanagement-ui', [
            \Simplydigital\CaseManagement\View\Components\Icon::class,
        ]);
    }

    public function loadRoutes()
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
    }

    public function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database');
    }

    public function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/../Config/case-management.php' => config_path('case-management.php'),
        ], 'case-management-config');
    } 

    public function publishResources()
    {
        $this->publishes([
            __DIR__.'/../Resources/icons' => resource_path('Simplydigital/CaseManagement/icons')
        ], 'simplydigital-casemanagement-resources');        
    }
    
    public function registerBladeComponents()
    {
    
    }
    
    public function registerLivewireComponents()
    {
        
    }    

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

    }
}