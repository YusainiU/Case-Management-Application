<?php

namespace Simplydigital\CaseManagement\Provider;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Query\Builder;
use Simplydigital\CaseManagement\Middleware\RestrictInternalUserOnly;

use Simplydigital\CaseManagement\Services\{
    CaseManagementUtilityServices,
    CaseTicketServices,
    CaseCustomerServices,
    CaseAssetServices,
    CaseProductService,
    CaseSupplierService
};

use Simplydigital\CaseManagement\Livewire\Customer\{
    CreateCustomer,
    ViewCustomers,
    CustomerProfile,
    DeleteCustomer,
    CustomerContacts,
    CreateCustomerContact,
    UpdateCustomerContact,
    CustomerSummary
};

use Simplydigital\CaseManagement\Livewire\Asset\{
    ViewAssets,
    AssetProfile,
    CreateAsset
};

use Simplydigital\CaseManagement\Livewire\Product\{
    ViewProducts,
    CreateProduct,
    ProductProfile
};

use Simplydigital\CaseManagement\Livewire\Supplier\{
    ViewSuppliers,
    CreateSupplier,
    SupplierProfile
};

use Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement\{
    CreateServiceLevelAgreement,
    ViewServiceLevelAgreement,
    ServiceLevelAgreementProfile,
    ViewSlaImplementation,
    CreateSlaImplementation,
    ImplementationProfile
};

use Simplydigital\CaseManagement\Livewire\Case\{
    ViewCases,
    CreateCase,
    CaseSummary,
    CaseStatus,
    CaseSla,
    ManageCase,
    ManageCaseSLA,
    CaseProgressLog,
};

class CaseManagementServiceProvider extends ServiceProvider
{

    private array $components = [
        'create-customer' => CreateCustomer::class,
        'view-customer' => ViewCustomers::class,
        'customer-profile' => CustomerProfile::class,
        'delete-customer' => DeleteCustomer::class,
        'customer-contacts' => CustomerContacts::class,
        'create-update-customer-contact' => CreateCustomerContact::class,
        'update-customer-contact' => UpdateCustomerContact::class,
        'view-assets' => ViewAssets::class,
        'view-products' => ViewProducts::class,
        'create-product' => CreateProduct::class,
        'product-profile' => ProductProfile::class,
        'view-suppliers' => ViewSuppliers::class,
        'create-supplier' => CreateSupplier::class,
        'supplier-profile' => SupplierProfile::class,
        'create-asset' => CreateAsset::class,
        'asset-profile' => AssetProfile::class,
        'create-sla' => CreateServiceLevelAgreement::class,
        'view-service-level-agreement' => ViewServiceLevelAgreement::class,
        'sla-profile' => ServiceLevelAgreementProfile::class,
        'view-sla-application' => ViewSlaImplementation::class,
        'create-sla-implementation' => CreateSlaImplementation::class,
        'customer-summary' => CustomerSummary::class,
        'sla-implementation-profile' => ImplementationProfile::class,
        'view-cases' => ViewCases::class,
        'create-case' => CreateCase::class,
        'case-summary' => CaseSummary::class,
        'case-status' => CaseStatus::class,
        'case-sla' => CaseSla::class,
        'manage-case' => ManageCase::class,
        'manage-case-sla' => ManageCaseSLA::class,
        'case-progress-log' => CaseProgressLog::class,
    ];

    private array $services = [
        'case-management-utility-services' => CaseManagementUtilityServices::class,
        'case-ticket-services'             => CaseTicketServices::class,
        'case-customer-services'           => CaseCustomerServices::class,
        'case-asset-services'              => CaseAssetServices::class,
        'case-product-services'            => CaseProductService::class,
        'case-supplier-services'           => CaseSupplierService::class,
    ];    

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
        foreach ($this->components as $name => $class) {
            Livewire::component($name, $class);
        }          
    }
    
    public function registerServices()
    {
        foreach ($this->services as $key => $service) {
            $this->app->singleton($key, fn () => new $service());
        }        
    }


}