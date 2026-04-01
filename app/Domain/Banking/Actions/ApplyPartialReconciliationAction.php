<?php

namespace App\Domain\Banking\Actions;

use App\Domain\Banking\Data\PartialReconciliationData;
use App\Models\BankTransaction;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class ApplyPartialReconciliationAction
{
    public function handle(PartialReconciliationData $data): array
    {
        return DB::transaction(function () use ($data) {
            $transaction = BankTransaction::findOrFail($data->bankTransactionId);
            $invoice = Invoice::findOrFail($data->invoiceId);

            if ($transaction->amount >= 0 || $invoice->total >= 0) {
                throw new \RuntimeException('Only negative transactions can reconcile negative invoices.');
            }

            if ($data->appliedAmount >= 0) {
                throw new \RuntimeException('Applied amount must be negative (cents).');
            }

            $txApplied = $transaction->invoices()->sum('bank_transaction_invoice.applied_amount');
            $invApplied = $invoice->bankTransactions()->sum('bank_transaction_invoice.applied_amount');

            $txRemaining = $transaction->amount - $txApplied;
            $invRemaining = $invoice->total - $invApplied;

            $maxApplicable = min(abs($txRemaining), abs($invRemaining));

            if (abs($data->appliedAmount) > $maxApplicable) {
                throw new \RuntimeException('Applied amount exceeds remaining balances.');
            }

            $transaction->invoices()->attach($invoice->id, [
                'applied_amount' => $data->appliedAmount,
            ]);

            // Recompute remaining after apply
            $newTxApplied = $txApplied + $data->appliedAmount;
            $newInvApplied = $invApplied + $data->appliedAmount;

            $transaction->update([
                'reconciled' => ($transaction->amount - $newTxApplied) === 0,
            ]);

            $invoice->update([
                'status' => ($invoice->total - $newInvApplied) === 0 ? 'paid' : $invoice->status,
            ]);

            return [
                'transaction_remaining' => $transaction->amount - $newTxApplied,
                'invoice_remaining' => $invoice->total - $newInvApplied,
            ];
        });
    }
}
