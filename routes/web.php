<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', \App\Livewire\Dashboard::class)->name('dashboard');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Invoice routes
    Route::get('/invoices', \App\Livewire\Invoices\InvoiceList::class)->name('invoices.index');
    Route::get('/invoices/create', \App\Livewire\Invoices\InvoiceEditor::class)->name('invoices.create');
    Route::get('/invoices/{invoice}/edit', \App\Livewire\Invoices\InvoiceEditor::class)->name('invoices.edit');
    Route::get('/invoices/{invoice}', function (\App\Models\Invoice $invoice) {
        $this->authorize('view', $invoice);
        return view('invoices.show', ['invoice' => $invoice->load('lines', 'customer')]);
    })->name('invoices.show');

    // Placeholder routes for other modules (used in sidebar)
    Route::get('/customers', fn() => view('customers.index'))->name('customers.index');
    Route::get('/customers/{customer}', fn(\App\Models\Customer $customer) => view('customers.show', ['customer' => $customer]))->name('customers.show');

    // Expenses routes
    Route::get('/expenses', \App\Livewire\Expenses\ExpenseList::class)->name('expenses.index');
    Route::get('/expenses/create', \App\Livewire\Expenses\ExpenseCreate::class)->name('expenses.create');

    // Banking routes
    Route::get('/banking', fn() => view('banking.index'))->name('banking.index');
    Route::get('/banking/import', \App\Livewire\Banking\BankImport::class)->name('banking.import');
    Route::get('/banking/reconcile', \App\Livewire\Banking\ReconciliationBoard::class)->name('banking.reconcile');

    // Tickets routes
    Route::get('/tickets', \App\Livewire\Tickets\TicketList::class)->name('tickets.index');
    Route::get('/tickets/create', fn() => view('tickets.create'))->name('tickets.create');
    Route::get('/tickets/{ticket}', \App\Livewire\Tickets\TicketThread::class)->name('tickets.show');
});

require __DIR__.'/auth.php';
