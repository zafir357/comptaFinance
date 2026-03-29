<?php

namespace App\Domain\Billing\Invoices\Actions;

use App\Domain\Billing\Invoices\Data\InvoiceData;
use App\Domain\Billing\Invoices\Services\InvoiceNumberingService;
use App\Models\Invoice;
use App\Models\InvoiceLine;

class CreateInvoiceAction
{
    public function __construct(
        private InvoiceNumberingService $numberingService
    ) {}

    public function execute(InvoiceData $data, int $organizationId): Invoice
    {
        $invoice = Invoice::create([
            'organization_id' => $organizationId,
            'customer_id' => $data->customerId,
            'number' => $this->numberingService->generate($organizationId),
            'issue_date' => $data->issueDate,
            'due_date' => $data->dueDate,
            'notes' => $data->notes,
            'status' => 'draft',
            'subtotal' => 0,
            'vat_total' => 0,
            'total' => 0,
        ]);

        $subtotal = 0;
        $vatTotal = 0;

        foreach ($data->lines as $line) {
            $quantity = (float) ($line['quantity'] ?? 1);
            $unitPrice = (int) ($line['unit_price'] ?? 0); // in centimes
            $vatRate = (float) ($line['vat_rate'] ?? 20.00);

            $lineTotal = (int) ($quantity * $unitPrice);
            $lineVat = (int) ($lineTotal * ($vatRate / 100));

            InvoiceLine::create([
                'invoice_id' => $invoice->id,
                'description' => $line['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'vat_rate' => $vatRate,
                'total' => $lineTotal,
            ]);

            $subtotal += $lineTotal;
            $vatTotal += $lineVat;
        }

        $invoice->update([
            'subtotal' => $subtotal,
            'vat_total' => $vatTotal,
            'total' => $subtotal + $vatTotal,
        ]);

        return $invoice->fresh(['lines', 'customer']);
    }
}
