<?php

namespace Simplydigital\Expenses\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Simplydigital\Expenses\Authorization\ExpensesPermissionProvider;
use Simplydigital\Expenses\Livewire\ExpenseAdmin;
use Simplydigital\Expenses\Livewire\ExpenseClaims;
use Simplydigital\Expenses\Models\Expense;
use Simplydigital\Expenses\Policies\ExpensePolicy;
use Simplydigital\UserManagement\Services\PermissionRegistry;

class ExpensesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->afterResolving(
            PermissionRegistry::class,
            function (PermissionRegistry $registry): void {
                $registry->registerProvider(new ExpensesPermissionProvider);
            },
        );
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'expenses');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        Route::middleware('web')->group(__DIR__.'/../Routes/web.php');

        Livewire::component('expense-claims', ExpenseClaims::class);
        Livewire::component('expense-admin', ExpenseAdmin::class);

        Gate::policy(Expense::class, ExpensePolicy::class);
    }
}
