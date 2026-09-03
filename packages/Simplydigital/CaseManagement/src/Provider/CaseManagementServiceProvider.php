<?php

namespace Simplydigital\CaseManagement\Provider;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Simplydigital\CaseManagement\Events\CasePlannerEventProvider;
use Simplydigital\CaseManagement\Events\SiteVisitEventProvider;
use Simplydigital\CaseManagement\Events\SlaExpiryEventProvider;
use Simplydigital\CaseManagement\Livewire\Asset\AssetProfile;
use Simplydigital\CaseManagement\Livewire\Asset\CreateAsset;
use Simplydigital\CaseManagement\Livewire\Asset\ViewAssets;
use Simplydigital\CaseManagement\Livewire\Case\CaseFieldChanges;
use Simplydigital\CaseManagement\Livewire\Case\CaseProgressLog;
use Simplydigital\CaseManagement\Livewire\Case\CaseSla;
use Simplydigital\CaseManagement\Livewire\Case\CaseStatus;
use Simplydigital\CaseManagement\Livewire\Case\CaseSummary;
use Simplydigital\CaseManagement\Livewire\Case\CloseAndResolveCase;
use Simplydigital\CaseManagement\Livewire\Case\CreateCase;
use Simplydigital\CaseManagement\Livewire\Case\CreateSiteVisit;
use Simplydigital\CaseManagement\Livewire\Case\Dashboard;
use Simplydigital\CaseManagement\Livewire\Case\ManageCase;
use Simplydigital\CaseManagement\Livewire\Case\ManageCaseSLA;
use Simplydigital\CaseManagement\Livewire\Case\ViewCases;
use Simplydigital\CaseManagement\Livewire\Case\ViewSiteVisit;
use Simplydigital\CaseManagement\Livewire\Case\ViewSlaPause;
use Simplydigital\CaseManagement\Livewire\Customer\CreateCustomer;
use Simplydigital\CaseManagement\Livewire\Customer\CreateCustomerContact;
use Simplydigital\CaseManagement\Livewire\Customer\CustomerContacts;
use Simplydigital\CaseManagement\Livewire\Customer\CustomerFiles;
use Simplydigital\CaseManagement\Livewire\Customer\CustomerProfile;
use Simplydigital\CaseManagement\Livewire\Customer\CustomerSummary;
use Simplydigital\CaseManagement\Livewire\Customer\DeleteCustomer;
use Simplydigital\CaseManagement\Livewire\Customer\UpdateCustomerContact;
use Simplydigital\CaseManagement\Livewire\Customer\ViewCustomers;
use Simplydigital\CaseManagement\Livewire\Product\CreateProduct;
use Simplydigital\CaseManagement\Livewire\Product\ProductProfile;
use Simplydigital\CaseManagement\Livewire\Product\ViewProducts;
use Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement\CreateServiceLevelAgreement;
use Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement\CreateSlaImplementation;
use Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement\ImplementationProfile;
use Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement\ServiceLevelAgreementProfile;
use Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement\ViewServiceLevelAgreement;
use Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement\ViewSlaImplementation;
use Simplydigital\CaseManagement\Livewire\Supplier\CreateSupplier;
use Simplydigital\CaseManagement\Livewire\Supplier\SupplierProfile;
use Simplydigital\CaseManagement\Livewire\Supplier\ViewSuppliers;
use Simplydigital\CaseManagement\Middleware\RestrictInternalUserOnly;
use Simplydigital\CaseManagement\Services\CaseAssetServices;
use Simplydigital\CaseManagement\Services\CaseCustomerServices;
use Simplydigital\CaseManagement\Services\CaseManagementUtilityServices;
use Simplydigital\CaseManagement\Services\CaseProductService;
use Simplydigital\CaseManagement\Services\CaseSupplierService;
use Simplydigital\CaseManagement\Services\CaseTicketServices;
use Simplydigital\CaseManagement\View\Components\Icon;
use Simplydigital\EventPlannerManagement\Facade\PlannerFacade;

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
        'case-field-changes' => CaseFieldChanges::class,
        'case-sla-pause' => ViewSlaPause::class,
        'view-site-visit' => ViewSiteVisit::class,
        'create-site-visit' => CreateSiteVisit::class,
        'close-and-resolve-case' => CloseAndResolveCase::class,
        'dashboard' => Dashboard::class,
        'customer-files' => CustomerFiles::class,
    ];

    private array $services = [
        'case-management-utility-services' => CaseManagementUtilityServices::class,
        'case-ticket-services' => CaseTicketServices::class,
        'case-customer-services' => CaseCustomerServices::class,
        'case-asset-services' => CaseAssetServices::class,
        'case-product-services' => CaseProductService::class,
        'case-supplier-services' => CaseSupplierService::class,
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
        $this->loadEventPlanner();

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

    public function loadEventPlanner()
    {
        if (class_exists(PlannerFacade::class)) {
            PlannerFacade::registerProvider(
                app(CasePlannerEventProvider::class)
            );

            PlannerFacade::registerProvider(
                app(SlaExpiryEventProvider::class)
            );

            PlannerFacade::registerProvider(
                app(SiteVisitEventProvider::class)
            );
        }

    }

    public function register(): void
    {
        $this->registerServices();
    }

    public function loadViews()
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'casemanagement');
        $this->loadViewComponentsAs('casemanagement-ui', [
            Icon::class,
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
            __DIR__.'/../Config/case-management.php' => config_path('case-management.php'),
        ], 'case-management-config');
    }

    public function publishResources()
    {
        $this->publishes([
            __DIR__.'/../Resources/icons' => resource_path('Simplydigital/CaseManagement/icons'),
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
            $this->app->singleton($key, fn () => new $service);
        }
    }
}
