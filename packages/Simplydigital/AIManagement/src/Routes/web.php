<?php

use Illuminate\Support\Facades\Route;
use Simplydigital\AIManagement\Livewire\ApprovalReviewQueue;

Route::get('/ai/approvals', ApprovalReviewQueue::class)
    ->middleware([
        'web',
        'auth',
        'verified',
        'permission:case.view',
        'permission:ai.proposals.view',
    ])
    ->name('ai.approvals.index');
