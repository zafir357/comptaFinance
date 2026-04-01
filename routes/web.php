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
        abort_unless(\Illuminate\Support\Facades\Gate::allows('view', $invoice), 403);
        return view('invoices.show', ['invoice' => $invoice->load('lines', 'customer')]);
    })->name('invoices.show');

    // Download invoice PDF
    Route::get('/invoices/{invoice}/pdf', function (\App\Models\Invoice $invoice) {
        abort_unless(\Illuminate\Support\Facades\Gate::allows('view', $invoice), 403);
        $pdfService = app(\App\Domain\Billing\Invoices\Services\InvoicePdfService::class);
        $pdfContent = $pdfService->handle($invoice);
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $invoice->number . '.pdf"',
        ]);
    })->name('invoices.download-pdf');

    // Mark a sent invoice as paid
    Route::post('/invoices/{invoice}/mark-paid', function (\App\Models\Invoice $invoice) {
        abort_unless(auth()->user()->organizations->contains($invoice->organization_id), 403);
        app(\App\Domain\Billing\Invoices\Actions\MarkInvoicePaidAction::class)->handle($invoice);
        return redirect()->route('invoices.show', $invoice)->with('success', 'Facture marquée comme payée.');
    })->name('invoices.mark-paid');

    // Delete a draft invoice
    Route::delete('/invoices/{invoice}', function (\App\Models\Invoice $invoice) {
        abort_unless(\Illuminate\Support\Facades\Gate::allows('delete', $invoice), 403);
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Facture supprimée.');
    })->name('invoices.destroy');

    // Placeholder routes for other modules (used in sidebar)
    Route::get('/customers', fn() => view('customers.index'))->name('customers.index');
    Route::get('/customers/{customer}', fn(\App\Models\Customer $customer) => view('customers.show', ['customer' => $customer]))->name('customers.show');

    // Expenses routes
    Route::get('/expenses', \App\Livewire\Expenses\ExpenseList::class)->name('expenses.index');
    Route::get('/expenses/create', \App\Livewire\Expenses\ExpenseCreate::class)->name('expenses.create');
    Route::get('/expenses/{expense}', function (\App\Models\Expense $expense) {
        abort_unless(\Illuminate\Support\Facades\Gate::allows('view', $expense), 403);
        return view('expenses.show', compact('expense'));
    })->name('expenses.show');
    Route::get('/expenses/{expense}/edit', function (\App\Models\Expense $expense) {
        abort_unless(\Illuminate\Support\Facades\Gate::allows('update', $expense), 403);
        return view('expenses.edit', compact('expense'));
    })->name('expenses.edit');
    Route::delete('/expenses/{expense}', function (\App\Models\Expense $expense) {
        abort_unless(\Illuminate\Support\Facades\Gate::allows('delete', $expense), 403);
        if ($expense->receipt_path) {
            \Storage::disk('receipts')->delete($expense->receipt_path);
        }
        $expense->delete();
        return redirect()->route('expenses.index')->with('message', 'Dépense supprimée.');
    })->name('expenses.destroy');
    Route::get('/expenses/{expense}/receipt', function (\App\Models\Expense $expense) {
        abort_unless(\Illuminate\Support\Facades\Gate::allows('view', $expense), 403);
        if (!$expense->receipt_path || !\Storage::disk('receipts')->exists($expense->receipt_path)) {
            abort(404);
        }
        return \Storage::disk('receipts')->download($expense->receipt_path);
    })->name('expenses.download-receipt');

    // Banking routes
    Route::get('/banking', \App\Livewire\Banking\ReconciliationBoard::class)->name('banking.index');
    Route::get('/banking/import', [\App\Http\Controllers\BankImportController::class, 'index'])->name('banking.import');
    Route::post('/banking/preview', [\App\Http\Controllers\BankImportController::class, 'preview'])->name('banking.preview');
    Route::post('/banking/import', [\App\Http\Controllers\BankImportController::class, 'import'])->name('banking.import.store');

    // Tickets routes
    Route::get('/tickets', \App\Livewire\Tickets\TicketList::class)->name('tickets.index');
    Route::get('/tickets/create', fn() => view('tickets.create'))->name('tickets.create');
    Route::get('/tickets/{ticket}', \App\Livewire\Tickets\TicketThread::class)->name('tickets.show');
});

require __DIR__.'/auth.php';
