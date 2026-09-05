<?php

use Spatie\Permission\Middleware\RoleMiddleware;
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
    ->name('engineer_dashboard');    

Route::middleware([
    'auth',
    'verified',
])
    ->group(function () {

        Route::redirect('settings', 'settings/profile');

        Route::middleware([
            RoleMiddleware::class.':Engineer Administrator',
        ])->group(function(){
            Route::get('engineer-management', EngineerManagement::class)->name('engineer-management');
            Route::get('engineer-skills', EngineerSkills::class)->name('engineer-skills');
            Route::get('engineer-certificates', EngineerCertificates::class)->name('engineer-certificates');
        });


        Route::middleware([
            RoleMiddleware::class.':User Administrator',
        ])->group(function(){   
            Route::get('user-management', App\Livewire\UserManagement::class)->name('user-management');
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
