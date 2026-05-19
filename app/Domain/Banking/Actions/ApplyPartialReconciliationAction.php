<?php

namespace App\Domain\Banking\Actions;

use App\Domain\Banking\Data\PartialReconciliationData;
use App\Domain\Banking\Data\ReconciliationResultData;
use App\Domain\Banking\Repositories\BankTransactionRepository;
use App\Domain\Billing\Invoices\Repositories\InvoiceRepository;
use Illuminate\Support\Facades\DB;

/**
 * ACTION: ApplyPartialReconciliationAction
 *
 * Applies a partial or full reconciliation between a bank transaction and an invoice.
 * 
 * DDD FLOW:
 * - Input: PartialReconciliationData (DTO)
 * - Output: ReconciliationResultData (DTO)
 * - Uses: BankTransactionRepository, InvoiceRepository
 *
 * RULES:
 * - Transaction and invoice must have the same sign (both positive or both negative)
 * - Applied amount must match the sign of the transaction
 * - Applied amount cannot exceed remaining balances
 * - Transaction marked reconciled when remaining = 0
 * - Invoice marked paid when remaining = 0
 */
class ApplyPartialReconciliationAction
{
    public function __construct(
        private BankTransactionRepository $transactionRepository,
        private InvoiceRepository $invoiceRepository,
    ) {}

    public function handle(PartialReconciliationData $data): ReconciliationResultData
    {
        return DB::transaction(function () use ($data) {
            $transaction = $this->transactionRepository->findOrFail($data->bankTransactionId);
            $invoice = $this->invoiceRepository->findOrFail($data->invoiceId);

            // Validate: Transaction and invoice must have the same sign
            $txIsPositive = $transaction->amount > 0;
            $invIsPositive = $invoice->total > 0;

            if ($txIsPositive !== $invIsPositive) {
                throw new \RuntimeException('Transaction and invoice must have the same sign (both positive or both negative).');
            }

            // Validate: Applied amount must match the sign
            if ($txIsPositive && $data->appliedAmount <= 0) {
                throw new \RuntimeException('Applied amount must be positive for positive transactions.');
            }
            if (!$txIsPositive && $data->appliedAmount >= 0) {
                throw new \RuntimeException('Applied amount must be negative for negative transactions.');
            }

            // Calculate current applied amounts
            $txApplied = $transaction->invoices()->sum('bank_transaction_invoice.applied_amount');
            $invApplied = $invoice->bankTransactions()->sum('bank_transaction_invoice.applied_amount');

            $txRemaining = $transaction->amount - $txApplied;
            $invRemaining = $invoice->total - $invApplied;

            $maxApplicable = min(abs($txRemaining), abs($invRemaining));

            if (abs($data->appliedAmount) > $maxApplicable) {
                throw new \RuntimeException('Applied amount exceeds remaining balances.');
            }

            // Attach via pivot table
            $transaction->invoices()->attach($invoice->id, [
                'applied_amount' => $data->appliedAmount,
            ]);

            // Calculate new remaining after apply
            $newTxApplied = $txApplied + $data->appliedAmount;
            $newInvApplied = $invApplied + $data->appliedAmount;

            $newTxRemaining = $transaction->amount - $newTxApplied;
            $newInvRemaining = $invoice->total - $newInvApplied;

            $txFullyReconciled = $newTxRemaining === 0;
            $invFullyPaid = $newInvRemaining === 0;

            // Update transaction status
            $this->transactionRepository->update($transaction, [
                'reconciled' => $txFullyReconciled,
            ]);

            // Update invoice status
            if ($invFullyPaid) {
                $this->invoiceRepository->update($invoice, [
                    'status' => 'paid',
                ]);
            }

            return new ReconciliationResultData(
                transactionId: $transaction->id,
                invoiceId: $invoice->id,
                appliedAmount: $data->appliedAmount,
                transactionRemaining: $newTxRemaining,
                invoiceRemaining: $newInvRemaining,
                transactionFullyReconciled: $txFullyReconciled,
                invoiceFullyPaid: $invFullyPaid,
            );
        });
    }
}
