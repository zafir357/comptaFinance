<?php

namespace App\Livewire\Banking;

use App\Domain\Banking\Actions\ApplyPartialReconciliationAction;
use App\Domain\Banking\Data\PartialReconciliationData;
use App\Domain\Banking\Repositories\BankTransactionRepository;
use App\Domain\Billing\Invoices\Repositories\InvoiceRepository;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * LIVEWIRE COMPONENT: ReconciliationBoard
 *
 * UI Controller for bank reconciliation.
 * Uses DDD pattern: Repository for queries, Action for business logic, DTO for data transfer.
 */
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

        $transactionRepo = app(BankTransactionRepository::class);
        $tx = $transactionRepo->find($this->selectedTransactionId);
        
        [$txRemaining, $invRemaining] = $this->remainingBalances();
        
        // Default to max applicable with correct sign
        $maxAmount = min(abs($txRemaining), abs($invRemaining));
        $signedAmount = $tx->amount > 0 ? $maxAmount : -$maxAmount;
        $this->appliedAmount = number_format($signedAmount / 100, 2, '.', '');
    }

    private function remainingBalances(): array
    {
        $transactionRepo = app(BankTransactionRepository::class);
        $invoiceRepo = app(InvoiceRepository::class);

        $tx = $transactionRepo->query()
            ->withSum('invoices as applied_sum', 'bank_transaction_invoice.applied_amount')
            ->findOrFail($this->selectedTransactionId);
        
        $invoice = $invoiceRepo->query()
            ->withSum('bankTransactions as applied_sum', 'bank_transaction_invoice.applied_amount')
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

        $transactionRepo = app(BankTransactionRepository::class);
        $tx = $transactionRepo->find($this->selectedTransactionId);
        
        $amountEuros = $this->appliedAmount ?? '0';
        $amountCents = (int) round((float) $amountEuros * 100);

        [$txRemaining, $invRemaining] = $this->remainingBalances();

        // Validate sign matches transaction sign
        $txIsPositive = $tx->amount > 0;
        if ($txIsPositive && $amountCents <= 0) {
            $this->addError('appliedAmount', 'Le montant doit être positif pour une transaction positive.');
            return;
        }
        if (!$txIsPositive && $amountCents >= 0) {
            $this->addError('appliedAmount', 'Le montant doit être négatif pour une transaction négative.');
            return;
        }

        $maxApplicable = min(abs($txRemaining), abs($invRemaining));
        if (abs($amountCents) > $maxApplicable) {
            $this->addError('appliedAmount', 'Montant dépasse le restant disponible.');
            return;
        }

        try {
            $action = app(ApplyPartialReconciliationAction::class);
            $action->handle(new PartialReconciliationData(
                bankTransactionId: $this->selectedTransactionId,
                invoiceId: $this->selectedInvoiceId,
                appliedAmount: $amountCents,
            ));

            $this->resetReconciliation();
            session()->flash('success', 'Transaction rapprochée avec succès!');
        } catch (\RuntimeException $e) {
            $this->addError('appliedAmount', $e->getMessage());
        }
    }

    public function render()
    {
        $transactionRepo = app(BankTransactionRepository::class);
        $invoiceRepo = app(InvoiceRepository::class);

        // Build transaction query using repository
        $query = $transactionRepo->query();

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
            ? $transactionRepo->find($this->selectedTransactionId) 
            : null;

        // Filter invoices by same sign as selected transaction
        $invoices = collect();
        if ($selectedTransaction) {
            $sign = $selectedTransaction->amount > 0 ? '>' : '<';
            $invoices = $invoiceRepo->query()->where('total', $sign, 0)->get();
        }

        return view('livewire.banking.reconciliation-board', [
            'transactions' => $transactions,
            'invoices' => $invoices,
            'selectedTransaction' => $selectedTransaction,
        ]);
    }
}
