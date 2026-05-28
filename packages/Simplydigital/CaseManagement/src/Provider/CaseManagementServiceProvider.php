<?php

namespace Simplydigital\CaseManagement\Provider;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Query\Builder;
use Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement\CreateSlaImplementation;
use Simplydigital\CaseManagement\Services\CaseManagementUtilityServices;
use Simplydigital\CaseManagement\Services\CaseTicketServices;
use Simplydigital\CaseManagement\Services\CaseCustomerServices;
use Simplydigital\CaseManagement\Services\CaseAssetServices;
use Simplydigital\CaseManagement\Services\CaseProductService;
use Simplydigital\CaseManagement\Services\CaseSupplierService;

use Simplydigital\CaseManagement\Livewire\Customer\CreateCustomer;
use Simplydigital\CaseManagement\Livewire\Customer\ViewCustomers;
use Simplydigital\CaseManagement\Livewire\Customer\CustomerProfile;
use Simplydigital\CaseManagement\Livewire\Customer\DeleteCustomer;
use Simplydigital\CaseManagement\Livewire\Customer\CustomerContacts;
use Simplydigital\CaseManagement\Livewire\Customer\CreateCustomerContact;
use Simplydigital\CaseManagement\Livewire\Customer\UpdateCustomerContact;
use Simplydigital\CaseManagement\Livewire\Asset\ViewAssets;
use Simplydigital\CaseManagement\Livewire\Asset\AssetProfile;
use Simplydigital\CaseManagement\Livewire\Asset\CreateAsset;
use Simplydigital\CaseManagement\Livewire\Case\ViewCases;
use Simplydigital\CaseManagement\Livewire\Case\CreateCase;
use Simplydigital\CaseManagement\Livewire\Customer\CustomerSummary;
use Simplydigital\CaseManagement\Livewire\Product\ViewProducts;
use Simplydigital\CaseManagement\Livewire\Product\CreateProduct;
use Simplydigital\CaseManagement\Livewire\Product\ProductProfile;
use Simplydigital\CaseManagement\Livewire\Supplier\ViewSuppliers;
use Simplydigital\CaseManagement\Livewire\Supplier\CreateSupplier;
use Simplydigital\CaseManagement\Livewire\Supplier\SupplierProfile;
use Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement\CreateServiceLevelAgreement;
use Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement\ViewServiceLevelAgreement;
use Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement\ServiceLevelAgreementProfile;
use Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement\ViewSlaImplementation;
use Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement\ImplementationProfile;
use Simplydigital\CaseManagement\Livewire\Case\CaseSummary;


use Simplydigital\CaseManagement\Middleware\RestrictInternalUserOnly;

class CaseManagementServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadViews();
        $this->loadRoutes();
        $this->loadMigrations();
        $this->publishConfig();
        $this->publishResources();
        $this->registerLivewireComponents();
        $this->addCustomeHelper();
        
    }
    /**
     * Register services.
     */

    public function addCustomeHelper()
    {
        Builder::macro('dumpRawSql', function () {
            dump($this->toRawSql());
            return $this;
        });        
    }

    public function register(): void
    {
        $this->registerServices();
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
        Route::middleware('web')->group(__DIR__.'/../Routes/web.php');
        Route::aliasMiddleware('restrict.internal.user', RestrictInternalUserOnly::class);   
    }

    public function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->publishes([
            __DIR__.'/../../database/seeders' => database_path('seeders/simplydigital/casemanagement'),
        ], 'casemanagement-seeders');

        $this->publishes([
            __DIR__.'/../../database/factories' => database_path('factories/simplydigital/casemanagement'),
        ], 'casemanagement-factories');        
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
        
    public function registerLivewireComponents()
    {
        Livewire::component('create-customer', CreateCustomer::class);
        Livewire::component('view-customer', ViewCustomers::class);
        Livewire::component('customer-profile', CustomerProfile::class);
        Livewire::component('delete-customer', DeleteCustomer::class);
        Livewire::component('customer-contacts', CustomerContacts::class);
        Livewire::component('create-update-customer-contact', CreateCustomerContact::class);
        Livewire::component('update-customer-contact', UpdateCustomerContact::class);
        Livewire::component('view-assets', ViewAssets::class);
        Livewire::component('view-products', ViewProducts::class);
        Livewire::component('create-product', CreateProduct::class);
        Livewire::component('product-profile', ProductProfile::class);
        Livewire::component('view-suppliers', ViewSuppliers::class);
        Livewire::component('create-supplier', CreateSupplier::class);
        Livewire::component('supplier-profile', SupplierProfile::class);
        Livewire::component('create-asset', CreateAsset::class);
        Livewire::component('asset-profile', AssetProfile::class);
        Livewire::component('create-sla',CreateServiceLevelAgreement::class);
        Livewire::component('view-service-level-agreement', ViewServiceLevelAgreement::class);
        Livewire::component('sla-profile', ServiceLevelAgreementProfile::class);
        Livewire::component('view-sla-application', ViewSlaImplementation::class);
        Livewire::component('create-sla-implementation', CreateSlaImplementation::class);
        Livewire::component('customer-summary', CustomerSummary::class);
        Livewire::component('sla-implementation-profile', ImplementationProfile::class);
        Livewire::component('view-cases', ViewCases::class);
        Livewire::component('create-case', CreateCase::class);
        Livewire::component('case-summary', CaseSummary::class);
    }
    
    public function registerServices()
    {
        $this->app->singleton('case-management-utility-services', function () {
            return new CaseManagementUtilityServices();
        });
        $this->app->singleton('case-ticket-services', function () {
            return new CaseTicketServices();
        });
        $this->app->singleton('case-customer-services', function () {
            return new CaseCustomerServices();
        });
        $this->app->singleton('case-asset-services', function () {
            return new CaseAssetServices();
        });
        $this->app->singleton('case-product-services', function () {
            return new CaseProductService();
        });
        $this->app->singleton('case-supplier-services', function () {
            return new CaseSupplierService();
        });
    }


}