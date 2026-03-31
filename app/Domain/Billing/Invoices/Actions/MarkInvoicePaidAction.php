<?php

namespace App\Domain\Billing\Invoices\Actions;

use App\Domain\Billing\Invoices\Repositories\InvoiceRepository;
use App\Models\Invoice;

/**
 * ACTION: MarkInvoicePaidAction
 *
 * Marks an invoice as paid (status = 'paid', paid_at = now).
 */
class MarkInvoicePaidAction
{
    public function __construct(
        private InvoiceRepository $invoiceRepository,
    ) {}

    public function handle(Invoice $invoice): Invoice
    {
        return $this->invoiceRepository->update($invoice, [
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}
