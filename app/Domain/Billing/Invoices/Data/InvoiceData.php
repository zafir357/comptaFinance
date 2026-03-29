<?php

namespace App\Domain\Billing\Invoices\Data;

/**
 * DTO: InvoiceData
 *
 * Type-safe data container for invoice creation/updates.
 * All money amounts are in CENTS (integer).
 *
 * Usage:
 *   $data = InvoiceData::fromRequest($request);
 *   $invoice = CreateInvoiceAction::handle($data);
 */
class InvoiceData
{
    public function __construct(
        public int $customer_id,
        public \DateTime $issue_date,
        public \DateTime $due_date,
        public array $lines = [],  // Array of InvoiceLineData
        public ?string $notes = null,
        public string $status = 'draft',
    ) {}

    /**
     * Create from form request data.
     */
    public static function fromArray(array $data): self
    {
        $lines = array_map(
            fn (array $line) => new InvoiceLineData(
                description: $line['description'],
                quantity: (int) ($line['quantity'] * 100), // Convert to cents
                unit_price: (int) ($line['unit_price'] * 100),
                vat_rate: (int) $line['vat_rate'],
            ),
            $data['lines'] ?? []
        );

        return new self(
            customer_id: (int) $data['customer_id'],
            issue_date: \DateTime::createFromFormat('Y-m-d', $data['issue_date']),
            due_date: \DateTime::createFromFormat('Y-m-d', $data['due_date']),
            lines: $lines,
            notes: $data['notes'] ?? null,
            status: $data['status'] ?? 'draft',
        );
    }

    /**
     * Calculate totals for display/validation.
     */
    public function calculateTotals(): array
    {
        $subtotal = 0;
        $vat_total = 0;

        foreach ($this->lines as $line) {
            $line_total = $line->quantity * $line->unit_price;
            $vat_on_line = (int) ($line_total * $line->vat_rate / 10000);

            $subtotal += $line_total;
            $vat_total += $vat_on_line;
        }

        return [
            'subtotal' => $subtotal,
            'vat_total' => $vat_total,
            'total' => $subtotal + $vat_total,
        ];
    }
}

class InvoiceLineData
{
    public function __construct(
        public string $description,
        public int $quantity,        // Cents
        public int $unit_price,      // Cents
        public int $vat_rate,        // Basis points (1% = 100)
    ) {}
}
