<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('logs:clear', function () {
    \Illuminate\Support\Facades\File::cleanDirectory(storage_path('logs'));
    $this->info('Logs cleared successfully.');
});
