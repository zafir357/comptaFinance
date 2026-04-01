<?php

namespace App\Livewire\Banking;

use App\Models\BankTransaction;
use App\Models\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ReconciliationBoard extends Component
{
    use WithPagination;

    public string $search = '';
    public string $tab = 'unreconciled';
    public ?int $selectedInvoiceId = null;
    public ?int $selectedTransactionId = null;
    public bool $showReconcileModal = false;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function selectTransaction(int $transactionId)
    {
        $this->selectedTransactionId = $transactionId;
        $this->selectedInvoiceId = null;
        $this->resetErrorBag('selectedInvoiceId');
        $this->showReconcileModal = true;
    }

    public function resetReconciliation(): void
    {
        $this->selectedTransactionId = null;
        $this->selectedInvoiceId = null;
        $this->resetErrorBag('selectedInvoiceId');
        $this->showReconcileModal = false;
    }

    public function reconcile()
    {
        if (!$this->selectedInvoiceId) {
            $this->addError('selectedInvoiceId', 'Sélectionner une facture');
            return;
        }

        if (!$this->selectedTransactionId) {
            return;
        }

        $transaction = BankTransaction::findOrFail($this->selectedTransactionId);
        $invoice = Invoice::findOrFail($this->selectedInvoiceId);

        // Create reconciliation using the correct singular relationship
        $transaction->reconciliation()->create([
            'reconcilable_type' => Invoice::class,
            'reconcilable_id' => $invoice->id,
            'amount' => $transaction->amount,
        ]);

        $transaction->update(['reconciled' => true]);

        $this->resetReconciliation();

        session()->flash('success', 'Transaction rapprochée avec succès!');
    }

    public function render()
    {
        $query = BankTransaction::query();

        if ($this->search) {
            $query->where('description', 'like', "%{$this->search}%");
        }

        if ($this->tab === 'unreconciled') {
            $query->where('reconciled', false);
        } else {
            $query->where('reconciled', true);
        }

        $transactions = $query->latest()->paginate(10);
        
        $selectedTransaction = $this->selectedTransactionId 
            ? BankTransaction::find($this->selectedTransactionId) 
            : null;

        return view('livewire.banking.reconciliation-board', [
            'transactions' => $transactions,
            'invoices' => Invoice::where('status', '!=', 'paid')->get(),
            'selectedTransaction' => $selectedTransaction,
        ]);
    }
}
