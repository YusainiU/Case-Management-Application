<?php

use Illuminate\Support\Facades\Route;
use Simplydigital\EventPlannerManagement\Middleware\RestrictInternalUserOnly;
use Simplydigital\EventPlannerManagement\Http\Controllers\PlannerController;
use Illuminate\Auth\Middleware\Authenticate;

const AUTH_MIDDLEWARE = Authenticate::class; // Define constant

Route::middleware([
        AUTH_MIDDLEWARE, // @phpstan-ignore-line
        'auth',
        'permission:event.view',
    ])
    ->prefix('eventManager')
    ->group(function () {
        Route::get(
            '/events',
            [PlannerController::class, 'index']
        )->name('eventmanagement.view');
    });    