<?php

use Illuminate\Support\Facades\Route;
use Simplydigital\CaseManagement\Livewire\Case\ManageCase;
use Simplydigital\CaseManagement\Livewire\Customer\ViewCustomers;
use Simplydigital\CaseManagement\Livewire\Customer\CustomerProfile;
use Simplydigital\CaseManagement\Livewire\Customer\CustomerContacts;
use Simplydigital\CaseManagement\Livewire\Product\ViewProducts;
use Simplydigital\CaseManagement\Livewire\Asset\ViewAssets;
use Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement\ViewServiceLevelAgreement;
use Simplydigital\CaseManagement\Livewire\ServiceLevelAgreement\ViewSlaImplementation;
use Simplydigital\CaseManagement\Middleware\RestrictInternalUserOnly;
use Simplydigital\CaseManagement\Livewire\Supplier\ViewSuppliers;
use Simplydigital\CaseManagement\Livewire\Case\ViewCases;
use Simplydigital\CaseManagement\Livewire\Case\ViewSiteVisit;
use Simplydigital\CaseManagement\Livewire\Case\Dashboard;


Route::middleware([
        'auth',
        RestrictInternalUserOnly::class,
    ])->group(function() {
            Route::get('/casemanagement/view-customers/{viewChildCompanies?}', ViewCustomers::class)->name('casemanagement.customers.view');
            Route::get('/casemanagement/customer-profile/{customerId}', CustomerProfile::class)
                ->name('casemanagement.customer.profile');
            Route::get('/casemanagement/customer-contacts/{customerId?}', CustomerContacts::class)
                ->name('casemanagement.customer.contacts');
            Route::get('/casemanagement/view-products', ViewProducts::class)->name('casemanagement.products.view');
            Route::get('/casemanagement/view-assets', ViewAssets::class)->name('casemanagement.assets.view');
            Route::get('/casemanagement/view-suppliers', ViewSuppliers::class)->name('casemanagement.suppliers.view');
            Route::get('/casemanagement/view-service-level-agreement', ViewServiceLevelAgreement::class)->name('casemanagement.serviceLevelAgreement.view');
            Route::get('/casemanagement/view-sla-application/{customerId?}', ViewSlaImplementation::class)->name('casemanagement.slaapplication.view');
            Route::get('/casemanagement/view-cases/{customerId?}', ViewCases::class)->name('casemanagement.cases.view');
            Route::get('/casemanagement/view-site-visit/{caseId?}', ViewSiteVisit::class)->name('casemanagement.sitevisit.view');
            Route::get('/casemanagement/dashboard', Dashboard::class)->name('casemanagement.dashboard.view');
            Route::get('/casemanagement/case/{case}', ManageCase::class)->name('casemanagement.cases.view');
    });
