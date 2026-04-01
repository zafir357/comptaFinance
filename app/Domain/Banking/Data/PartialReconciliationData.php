<?php

namespace App\Domain\Banking\Data;

class PartialReconciliationData
{
    public function __construct(
        public int $bankTransactionId,
        public int $invoiceId,
        public int $appliedAmount, // cents (negative for debits)
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self(
            bankTransactionId: (int) $payload['bank_transaction_id'],
            invoiceId: (int) $payload['invoice_id'],
            appliedAmount: (int) $payload['applied_amount'],
        );
    }
}
