<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard - Livewire component
    Route::get('dashboard', \App\Livewire\Dashboard::class)->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // TEST MULTI-TENANT (Livewire classique)
    Route::get('test-multi-tenant', \App\Livewire\TestMultiTenant::class)->name('test.multi-tenant');

    // MODULE CLIENTS
    Route::get('customers', \App\Livewire\Customers\CustomerList::class)->name('customers.index');
    Route::get('customers/create', \App\Livewire\Customers\CustomerCreate::class)->name('customers.create');

    // MODULE FACTURES
    Route::get('invoices', \App\Livewire\Invoices\InvoiceList::class)->name('invoices.index');
    Route::get('invoices/create', \App\Livewire\Invoices\InvoiceCreate::class)->name('invoices.create');
    Route::get('invoices/{invoice}', \App\Livewire\Invoices\InvoiceShow::class)->name('invoices.show');

    // MODULE DÉPENSES
    Route::get('expenses', \App\Livewire\Expenses\ExpenseList::class)->name('expenses.index');
    Route::get('expenses/create', \App\Livewire\Expenses\ExpenseCreate::class)->name('expenses.create');
});

require __DIR__.'/auth.php';
