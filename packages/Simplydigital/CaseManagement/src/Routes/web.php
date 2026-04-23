<?php

use Illuminate\Support\Facades\Route;
use Simplydigital\CaseManagement\Livewire\Customer\ViewCustomers;
use Simplydigital\CaseManagement\Livewire\Customer\CustomerProfile;
use Simplydigital\CaseManagement\Livewire\Customer\CustomerContacts;
use Simplydigital\CaseManagement\Livewire\Product\ViewProducts;
use Simplydigital\CaseManagement\Livewire\Asset\ViewAssets;
use Simplydigital\CaseManagement\Middleware\RestrictInternalUserOnly;
use Simplydigital\CaseManagement\Livewire\Supplier\ViewSuppliers;


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
            
    });
