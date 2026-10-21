<?php

use Illuminate\Support\Facades\Route;
use Simplydigital\StockManagement\Livewire\StockRequestQueue;

Route::get('/stock-requests', StockRequestQueue::class)
    ->middleware([
        'auth',
        'verified',
        'permission:stock_request.manage',
    ])
    ->name('stock-requests.index');
