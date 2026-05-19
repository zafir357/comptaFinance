<?php

namespace App\Domain\Banking\Data;

/**
 * DTO: ReconciliationResultData
 *
 * Carries the result of a reconciliation operation.
 * Used as return value from ApplyPartialReconciliationAction.
 */
class ReconciliationResultData
{
    public function __construct(
        public int $transactionId,
        public int $invoiceId,
        public int $appliedAmount,
        public int $transactionRemaining,
        public int $invoiceRemaining,
        public bool $transactionFullyReconciled,
        public bool $invoiceFullyPaid,
    ) {}

    public function toArray(): array
    {
        return [
            'transaction_id' => $this->transactionId,
            'invoice_id' => $this->invoiceId,
            'applied_amount' => $this->appliedAmount,
            'transaction_remaining' => $this->transactionRemaining,
            'invoice_remaining' => $this->invoiceRemaining,
            'transaction_fully_reconciled' => $this->transactionFullyReconciled,
            'invoice_fully_paid' => $this->invoiceFullyPaid,
        ];
    }
}
