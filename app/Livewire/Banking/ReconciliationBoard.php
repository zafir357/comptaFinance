<?php

namespace App\Livewire\Banking;

use App\Models\BankTransaction;
use App\Models\Invoice;
use App\Domain\Banking\Data\PartialReconciliationData;
use App\Domain\Banking\Actions\ApplyPartialReconciliationAction;
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
    public ?string $appliedAmount = null; // euros as string

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function selectTransaction(int $transactionId)
    {
        $this->selectedTransactionId = $transactionId;
        $this->selectedInvoiceId = null;
        $this->appliedAmount = null;
        $this->resetErrorBag('selectedInvoiceId');
        $this->showReconcileModal = true;
    }

    public function resetReconciliation(): void
    {
        $this->selectedTransactionId = null;
        $this->selectedInvoiceId = null;
        $this->appliedAmount = null;
        $this->resetErrorBag('selectedInvoiceId');
        $this->showReconcileModal = false;
    }

    public function updatedSelectedInvoiceId($value): void
    {
        if (!$this->selectedTransactionId || !$this->selectedInvoiceId) {
            return;
        }

        [$txRemaining, $invRemaining] = $this->remainingBalances();
        $default = -min(abs($txRemaining), abs($invRemaining));
        $this->appliedAmount = number_format($default / 100, 2, '.', '');
    }

    private function remainingBalances(): array
    {
        $tx = BankTransaction::withSum('invoices as applied_sum', 'bank_transaction_invoice.applied_amount')
            ->findOrFail($this->selectedTransactionId);
        $invoice = Invoice::withSum('bankTransactions as applied_sum', 'bank_transaction_invoice.applied_amount')
            ->findOrFail($this->selectedInvoiceId);

        $txRemaining = $tx->amount - ($tx->applied_sum ?? 0);
        $invRemaining = $invoice->total - ($invoice->applied_sum ?? 0);

        return [$txRemaining, $invRemaining];
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

        $amountEuros = $this->appliedAmount ?? '0';
        $amountCents = (int) round((float) $amountEuros * 100);

        [$txRemaining, $invRemaining] = $this->remainingBalances();

        if ($amountCents >= 0) {
            $this->addError('appliedAmount', 'Le montant doit être négatif.');
            return;
        }

        $maxApplicable = min(abs($txRemaining), abs($invRemaining));
        if (abs($amountCents) > $maxApplicable) {
            $this->addError('appliedAmount', 'Montant dépasse le restant disponible.');
            return;
        }

        /** @var ApplyPartialReconciliationAction $action */
        $action = app(ApplyPartialReconciliationAction::class);
        $action->handle(new PartialReconciliationData(
            bankTransactionId: $this->selectedTransactionId,
            invoiceId: $this->selectedInvoiceId,
            appliedAmount: $amountCents,
        ));

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
            'invoices' => Invoice::where('total', '<', 0)->get(),
            'selectedTransaction' => $selectedTransaction,
        ]);
    }
}
