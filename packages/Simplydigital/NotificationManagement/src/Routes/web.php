<?php

use Illuminate\Support\Facades\Route;
use Simplydigital\NotificationManagement\Middleware\RestrictInternalUserOnly;
use Simplydigital\NotificationManagement\Livewire\Admin\QueuedMailJobs;

Route::middleware([
        'auth',
        RestrictInternalUserOnly::class,
    ])->group(function() {
            Route::get('/notficationmanagement/EmailJobs', QueuedMailJobs::class)->name('notifcation.jobs.view');
    });
