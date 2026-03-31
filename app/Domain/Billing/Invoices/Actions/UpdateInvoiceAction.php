<?php

namespace App\Domain\Billing\Invoices\Actions;

use App\Domain\Billing\Invoices\Data\InvoiceData;
use App\Domain\Billing\Invoices\Repositories\InvoiceRepository;
use App\Models\Invoice;
use App\Models\InvoiceLine;

/**
 * ACTION: UpdateInvoiceAction
 *
 * Updates an existing invoice.
 * Only allows updates on draft invoices.
 */
class UpdateInvoiceAction
{
    public function __construct(
        private InvoiceRepository $invoiceRepository,
    ) {}

    public function handle(Invoice $invoice, InvoiceData $data): Invoice
    {
        // Only allow updates on draft invoices
        if ($invoice->status !== 'draft') {
            throw new \Exception('Can only update draft invoices.');
        }

        // Calculate totals
        $totals = $data->calculateTotals();

        // Update invoice via repository
        $invoice = $this->invoiceRepository->update($invoice, [
            'customer_id' => $data->customer_id,
            'issue_date' => $data->issue_date,
            'due_date' => $data->due_date,
            'subtotal' => $totals['subtotal'],
            'vat_total' => $totals['vat_total'],
            'total' => $totals['total'],
            'notes' => $data->notes,
        ]);

        // Delete old lines and create new ones
        $invoice->lines()->delete();

        foreach ($data->lines as $lineData) {
            $lineTotal = $lineData->quantity * $lineData->unit_price;
            $vat = (int) ($lineTotal * $lineData->vat_rate / 10000);

            InvoiceLine::create([
                'invoice_id' => $invoice->id,
                'description' => $lineData->description,
                'quantity' => $lineData->quantity,
                'unit_price' => $lineData->unit_price,
                'vat_rate' => $lineData->vat_rate,
                'total' => $lineTotal + $vat,
            ]);
        }

        return $invoice->load('lines', 'customer');
    }
}
