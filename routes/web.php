<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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
});

require __DIR__.'/auth.php';
