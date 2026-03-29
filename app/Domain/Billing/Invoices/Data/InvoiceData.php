<?php

namespace App\Domain\Billing\Invoices\Data;

class InvoiceData
{
    public function __construct(
        public int $customerId,
        public string $issueDate,
        public string $dueDate,
        public array $lines,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            customerId: (int) $data['customer_id'],
            issueDate: $data['issue_date'],
            dueDate: $data['due_date'],
            lines: $data['lines'] ?? [],
            notes: $data['notes'] ?? null,
        );
    }
}
