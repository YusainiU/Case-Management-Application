<?php

namespace Simplydigital\FileUploadManagement\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Simplydigital\FileUploadManagement\Models\UploadedFile;
use Simplydigital\UserManagement\Services\PermissionRegistry;
use Simplydigital\FileUploadManagement\Authorization\FileManagementPermissionProvider;
use Simplydigital\FileUploadManagement\Policies\FilePolicy;
use Simplydigital\FileUploadManagement\Contracts\FileStorage;
use Simplydigital\FileUploadManagement\Services\FilePathResolver;
use Simplydigital\FileUploadManagement\Services\FileStorageManager;

class FileUploadManagementServiceProvider extends ServiceProvider
{
    /**
     * Register package services.
     */
    public function register(): void
    {
        
        
        $this->publishes([
            __DIR__ . '/../../config/fileupload.php' => config_path('fileupload.php'),
        ], 'fileupload-config');        

        /*
         * Bind the storage contract to the application's
         * configured filesystem implementation.
         */
        $this->app->singleton(
            FileStorage::class,
            function () {
                return new FileStorageManager(
                    disk: config('fileupload.disk', 'local')
                );
            }
        );

        /*
         * FilePathResolver is stateless, so a singleton is sufficient.
         */
        $this->app->singleton(
            FilePathResolver::class,
            fn () => new FilePathResolver()
        );

        $this->registerUserPermissions();
    }

    public function registerUserPermissions()
    {
        $this->app->afterResolving(
            PermissionRegistry::class,
            function (PermissionRegistry $registry) {
                $registry->registerProvider(
                    new FileManagementPermissionProvider
                );
            }
        );

    }    

    /**
     * Bootstrap package services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/../../database/migrations'
        );

        $this->loadViewsFrom(
            __DIR__ . '/../Resources/views',
            'fileupload-management'
        );
        
        $this->loadPolicies();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/fileupload.php'
                    => config_path('fileupload.php'),
            ], 'fileupload-config');

            $this->publishes([
                __DIR__ . '/../../database/migrations'
                    => database_path('migrations'),
            ], 'fileupload-migrations');

            $this->publishes([
                __DIR__ . '/../Resources/views'
                    => resource_path('views/vendor/fileupload'),
            ], 'fileupload-views');
        }
    }

    public function loadPolicies()
    {
        Gate::policy(UploadedFile::class, FilePolicy::class);
    }      
}