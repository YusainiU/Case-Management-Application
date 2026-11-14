<?php

use Illuminate\Support\Facades\Route;
use Simplydigital\EventPlannerManagement\Middleware\RestrictInternalUserOnly;
use Simplydigital\EventPlannerManagement\Http\Controllers\PlannerController;
use Illuminate\Auth\Middleware\Authenticate;

Route::middleware([
        Authenticate::class,
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