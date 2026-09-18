<?php

use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use App\Livewire\EngineerManagement;
use App\Livewire\EngineerSkills;
use App\Livewire\EngineerCertificates;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use Simplydigital\CaseManagement\Livewire\Case\Dashboard;


Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', Dashboard::class)
    ->middleware([
        'auth',
        'verified',
    ])
    ->name('dashboard');

Route::view('/engineer-dashboard', 'dashboard')
    ->middleware([
        'auth',
        'verified',
    ])
    ->name('engineer_dashboard')
    ->middleware('permission:engineer.view');    

Route::middleware([
    'auth',
    'verified',
])
    ->group(function () {

        Route::redirect('settings', 'settings/profile');

        Route::middleware([
            'web',
            'auth',
        ])->group(function(){

            Route::get('engineer-management', EngineerManagement::class)
                ->name('engineer-management')
                ->middleware('permission:engineer.view');
            Route::get('engineer-skills', EngineerSkills::class)
                ->name('engineer-skills')
                ->middleware('permission:engineer.view');
            Route::get('engineer-certificates', EngineerCertificates::class)
                ->name('engineer-certificates')
                ->middleware('permission:engineer.view');
        });


        Route::middleware([
            'web',
            'auth',
        ])->group(function(){   
            Route::get('user-management', App\Livewire\UserManagement::class)
            ->name('user-management')
            ->middleware('permission:User.view');
        });        

        Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
        Volt::route('settings/password', 'settings.password')->name('user-password.edit');
        Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
        Volt::route('settings/two-factor', 'settings.two-factor')
            ->middleware(
                when(
                    Features::canManageTwoFactorAuthentication()
                        && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                    ['password.confirm'],
                    [],
                ),
            )
            ->name('two-factor.show');
    });
