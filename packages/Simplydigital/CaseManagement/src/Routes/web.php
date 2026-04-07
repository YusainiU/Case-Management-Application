<?php

use Illuminate\Support\Facades\Route;
use Simplydigital\CaseManagement\Livewire\Customer\ViewCustomers;
use Simplydigital\CaseManagement\Livewire\Customer\CustomerProfile;
use Simplydigital\CaseManagement\Livewire\Customer\CustomerContacts;
use Simplydigital\CaseManagement\Middleware\RestrictInternalUserOnly;


Route::middleware([
        'auth',
        RestrictInternalUserOnly::class,
    ])->group(function() {
            Route::get('/casemanagement/view-customers', ViewCustomers::class)->name('casemanagement.customers.view');
            Route::get('/casemanagement/customer-profile/{customerId}', CustomerProfile::class)
                ->name('casemanagement.customer.profile');
            Route::get('/casemanagement/customer-contacts/{customerId?}', CustomerContacts::class)
                ->name('casemanagement.customer.contacts');
    });
