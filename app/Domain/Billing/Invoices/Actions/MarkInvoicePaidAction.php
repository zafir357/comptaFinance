<?php

namespace App\Domain\Billing\Invoices\Actions;

use App\Models\Invoice;

/**
 * ACTION: MarkInvoicePaidAction
 *
 * Marks an invoice as paid (status = 'paid', paid_at = now).
 */
class MarkInvoicePaidAction
{
    public function handle(Invoice $invoice): Invoice
    {
        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return $invoice;
    }
}
