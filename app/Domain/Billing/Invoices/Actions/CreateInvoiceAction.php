<?php

namespace App\Domain\Billing\Invoices\Actions;

use App\Domain\Billing\Invoices\Data\InvoiceData;
use App\Domain\Billing\Invoices\Repositories\InvoiceRepository;
use App\Domain\Billing\Invoices\Services\InvoiceNumberingService;
use App\Models\Invoice;
use App\Models\InvoiceLine;

/**
 * ACTION: CreateInvoiceAction
 *
 * Creates a new invoice with all its line items.
 * Single-responsibility: handles invoice creation workflow.
 *
 * IMPORTANT: This is where we ensure:
 * - Organization is set (automatic via repository)
 * - Invoice number is sequential
 * - Totals are calculated correctly
 * - All line items are created
 *
 * Usage:
 *   $data = InvoiceData::fromArray($request->validated());
 *   $invoice = app(CreateInvoiceAction::class)->handle($data);
 */
class CreateInvoiceAction
{
    public function __construct(
        private InvoiceNumberingService $numberingService,
        private InvoiceRepository $invoiceRepository,
    ) {}

    public function handle(InvoiceData $data): Invoice
    {
        // 1. Calculate totals
        $totals = $data->calculateTotals();

        // 2. Get next invoice number
        $invoiceNumber = $this->numberingService->nextNumber();

        // 3. Create invoice via repository
        $invoice = $this->invoiceRepository->create([
            'customer_id' => $data->customer_id,
            'number' => $invoiceNumber,
            'status' => $data->status,
            'issue_date' => $data->issue_date,
            'due_date' => $data->due_date,
            'subtotal' => $totals['subtotal'],
            'vat_total' => $totals['vat_total'],
            'total' => $totals['total'],
            'notes' => $data->notes,
        ]);

        // 4. Create line items
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

        // 5. Reload with relations
        return $invoice->load('lines', 'customer');
    }
}
