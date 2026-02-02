<?php
namespace Simplydigital\UserManagement\Provider;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;

use Simplydigital\UserManagement\Http\Components\BladeAddNewUserComponent;

use Simplydigital\UserManagement\Livewire\AddNewUser;

class UserManagementServiceProvider extends ServiceProvider
{
    /**
    * Bootstrap any package services.
    * @return void  
    */
    public function boot()
    {
        $this->loadViews();
        $this->loadRoutes();
        $this->registerBladeComponents();
        $this->registerLivewireComponents();
    }

    public function loadViews()
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'usermanagement');
    }

    public function loadRoutes()
    {
        if (file_exists(__DIR__ . '/../Routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        }    
    }

    public function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/../Config/user-management.stub' => config_path('user-management.php'),
        ], 'user-management-config');
    }

    public function registerBladeComponents()
    {
        // Register Blade components here if needed
        Blade::component('blade-addnewuser', BladeAddNewUserComponent::class);
    }

    public function registerLivewireComponents()
    {
        // Register Livewire components here if needed
        Livewire::component('addnewuser-component', AddNewUser::class);
    }

    public function register()
    {
        
    }

}