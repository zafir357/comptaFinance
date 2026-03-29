<?php

namespace App\Livewire\Banking;

use App\Models\BankTransaction;
use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class ReconciliationBoard extends Component
{
    use WithPagination;

    public string $search = '';
    public string $tab = 'unreconciled'; // 'unreconciled' or 'reconciled'
    public ?int $selectedTransactionId = null;
    public ?int $selectedInvoiceId = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function reconcile()
    {
        if (!$this->selectedTransactionId || !$this->selectedInvoiceId) {
            return;
        }

        $transaction = BankTransaction::findOrFail($this->selectedTransactionId);
        $invoice = Invoice::findOrFail($this->selectedInvoiceId);

        $transaction->reconciliations()->create([
            'reconcilable_type' => Invoice::class,
            'reconcilable_id' => $invoice->id,
            'amount' => $transaction->amount,
        ]);

        $transaction->update(['reconciled' => true]);

        session()->flash('success', 'Transaction rapprochée avec succès!');
        $this->resetSelection();
    }

    public function resetSelection()
    {
        $this->selectedTransactionId = null;
        $this->selectedInvoiceId = null;
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

        return view('livewire.banking.reconciliation-board', [
            'transactions' => $transactions,
            'invoices' => Invoice::where('status', '!=', 'paid')->get(),
        ]);
    }
}
