<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Simplydigital\CaseManagement\Livewire\Asset\ViewAssets;
use Simplydigital\CaseManagement\Livewire\Case\Dashboard;
use Simplydigital\CaseManagement\Livewire\Case\ManageCase;
use Simplydigital\CaseManagement\Livewire\Case\ViewCases;
use Simplydigital\CaseManagement\Livewire\Case\ViewSiteVisit;
use Simplydigital\CaseManagement\Livewire\Customer\CustomerContacts;
use Simplydigital\CaseManagement\Livewire\Customer\CustomerProfile;
use Simplydigital\CaseManagement\Livewire\Customer\ViewCustomers;
use Simplydigital\CaseManagement\Livewire\Portal\CreateCase as PortalCreateCase;
use Simplydigital\CaseManagement\Livewire\Portal\Dashboard as PortalDashboard;
use Simplydigital\CaseManagement\Livewire\Portal\ViewCase as PortalViewCase;
use Simplydigital\CaseManagement\Livewire\Portal\ViewCases as PortalViewCases;
use Simplydigital\CaseManagement\Livewire\Product\ViewProducts;
use Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement\ViewServiceLevelAgreement;
use Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement\ViewSlaImplementation;
use Simplydigital\CaseManagement\Livewire\Supplier\ViewSuppliers;

Route::get('/portal/login', function (Request $request) {
    if ($request->user()) {
        return $request->user()->can('portal.access')
            ? redirect()->route('portal.dashboard')
            : redirect()->route('dashboard');
    }

    $request->session()->put(
        'url.intended',
        route('portal.dashboard', absolute: false)
    );

    return response()->view('casemanagement::livewire.portal.login');
})->name('portal.login');

Route::middleware([
    'auth',
    'permission:case.view',
])->group(function () {

    Route::get('/casemanagement/view-customers/{viewChildCompanies?}', ViewCustomers::class)
        ->name('casemanagement.customers.view')
        ->middleware('permission:customer.view');

    Route::get('/casemanagement/customer-profile/{customerId}', CustomerProfile::class)
        ->name('casemanagement.customer.profile')
        ->middleware('permission:customer.view');

    Route::get('/casemanagement/customer-contacts/{customerId?}', CustomerContacts::class)
        ->name('casemanagement.customer.contacts')
        ->middleware('permission:customer_contacts.view');

    Route::get('/casemanagement/view-products', ViewProducts::class)
        ->name('casemanagement.products.view')
        ->middleware('permission:product.view');

    Route::get('/casemanagement/view-assets', ViewAssets::class)
        ->name('casemanagement.assets.view')
        ->middleware('permission:asset.view');

    Route::get('/casemanagement/view-suppliers', ViewSuppliers::class)
        ->name('casemanagement.suppliers.view')
        ->middleware('permission:supplier.view');

    Route::get('/casemanagement/view-service-level-agreement', ViewServiceLevelAgreement::class)
        ->name('casemanagement.serviceLevelAgreement.view')
        ->middleware('permission:service_level_agreement.view');

    Route::get('/casemanagement/view-sla-application/{customerId?}', ViewSlaImplementation::class)
        ->name('casemanagement.slaapplication.view')
        ->middleware('permission:service_level_agreement.view');

    Route::get('/casemanagement/view-cases/{customerId?}', ViewCases::class)
        ->name('casemanagement.cases.view')
        ->middleware('permission:case.view');

    Route::get('/casemanagement/view-site-visit/{caseId?}', ViewSiteVisit::class)
        ->name('casemanagement.sitevisit.view')
        ->middleware('permission:case.view');

    Route::get('/casemanagement/dashboard', Dashboard::class)
        ->name('casemanagement.dashboard.view')
        ->middleware('permission:case.view');

    Route::get('/casemanagement/case/{case}', ManageCase::class)
        ->name('casemanagement.case.view')
        ->middleware('permission:case.view');
});

Route::prefix('portal')
    ->name('portal.')
    ->middleware('customer.portal')
    ->group(function (): void {
        Route::get('/', PortalDashboard::class)
            ->name('dashboard');

        Route::get('/cases', PortalViewCases::class)
            ->name('cases.index');

        Route::get('/cases/create', PortalCreateCase::class)
            ->name('cases.create');

        Route::get('/cases/{case}', PortalViewCase::class)
            ->whereNumber('case')
            ->name('cases.show');
    });
