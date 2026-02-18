<?php

use Illuminate\Support\Facades\Route;
use Simplydigital\UserManagement\Http\Controllers\PrivateImageController;

Route::middleware(['web', 'auth'])
    ->get('/usermanagement/private-image/{path}', [PrivateImageController::class, 'show'])
    ->where('path', '.*')
    ->name('usermanagement.private.image');