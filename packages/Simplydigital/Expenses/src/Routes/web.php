<?php

use Illuminate\Support\Facades\Route;
use Simplydigital\Expenses\Livewire\ExpenseAdmin;
use Simplydigital\Expenses\Livewire\ExpenseClaims;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/expenses', ExpenseClaims::class)
        ->middleware('permission:expense.view')
        ->name('expenses.index');

    Route::get('/expenses/admin', ExpenseAdmin::class)
        ->middleware('permission:expense.admin')
        ->name('expenses.admin');
});
