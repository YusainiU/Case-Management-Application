<?php

namespace Simplydigital\UserManagement\Provider;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Simplydigital\UserManagement\Http\Components\BladeAddNewUserComponent;
use Simplydigital\UserManagement\Http\Components\BladeListAllUserComponent;
use Simplydigital\UserManagement\Livewire\AddNewPermission;
use Simplydigital\UserManagement\Livewire\AddNewRole;
use Simplydigital\UserManagement\Livewire\AddNewUser;
use Simplydigital\UserManagement\Livewire\ListAllUser;
use Simplydigital\UserManagement\Livewire\ListPermissions;
use Simplydigital\UserManagement\Livewire\ListRoles;
use Simplydigital\UserManagement\Livewire\UpdateUser;
use Simplydigital\UserManagement\Livewire\UserProfile;
use Simplydigital\UserManagement\Services\PermissionRegistry;
use Simplydigital\UserManagement\Services\PermissionSynchronizer;
use Simplydigital\UserManagement\Services\UserManagementServices;
use Simplydigital\UserManagement\View\Components\Icon;

class UserManagementServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViews();
        $this->loadRoutes();
        $this->registerBladeComponents();
        $this->registerLivewireComponents();
        $this->publishMigrations();
        $this->publishConfig();
        $this->publishResources();
    }

    public function loadViews()
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'usermanagement');
        $this->loadViewComponentsAs('usermanagement-ui', [Icon::class]);
    }

    public function loadRoutes()
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');

    }

    public function publishMigrations()
    {
        $this->publishes([
            __DIR__.'/../Database/migrations/1_create_users_details_table.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_users_details_table.php'),
            __DIR__.'/../Database/migrations/2_create_users_statuses_table.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_users_statuses_table.php'),
            __DIR__.'/../Database/migrations/3_add_identification_field_to_user_details_table.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_users_roles_table.php'),
        ]);
    }

    public function publishResources()
    {
        $this->publishes([
            __DIR__.'/../Resources/icons' => resource_path('Simplydigital/UserManagement/icons'),
        ], 'simplydigital-usermanagement-resources');
    }

    public function publishConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/user-management.stub' => config_path('user-management.php'),
        ], 'user-management-config');
    }

    public function registerBladeComponents()
    {
        // Register Blade components here if needed
        Blade::component('blade-addnewuser', BladeAddNewUserComponent::class);
        Blade::component('blade-listalluser', BladeListAllUserComponent::class);
    }

    public function registerLivewireComponents()
    {
        // Register Livewire components here if needed
        Livewire::component('addnewuser-component', AddNewUser::class);
        Livewire::component('listalluser-component', ListAllUser::class);
        Livewire::component('updateuser-component', UpdateUser::class);
        Livewire::component('addnewpermission-component', AddNewPermission::class);
        Livewire::component('addnewrole-component', AddNewRole::class);
        Livewire::component('listpermissions-component', ListPermissions::class);
        Livewire::component('listroles-component', ListRoles::class);
        Livewire::component('userprofile-component', UserProfile::class);
    }

    public function register()
    {
        $this->app->singleton(
            PermissionRegistry::class,
            function () {
                return new PermissionRegistry;
            }
        );

        $this->app->singleton(
            PermissionSynchronizer::class
        );

        $this->app->singleton('usermanagementservices', function () {
            return new UserManagementServices;
        });
    }
}
