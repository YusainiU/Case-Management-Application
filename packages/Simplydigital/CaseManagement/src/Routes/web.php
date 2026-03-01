<?php

use Illuminate\Support\Facades\Route;
use Simplydigital\CaseManagement\Livewire\Customer\ViewCustomers;
use Simplydigital\CaseManagement\Middleware\RestrictInternalUserOnly;


Route::middleware([
        'auth',
        RestrictInternalUserOnly::class,
    ])->group(function() {
            Route::get('/casemanagement/view-customers', ViewCustomers::class)->name('casemanagement.view.customers');
    });
