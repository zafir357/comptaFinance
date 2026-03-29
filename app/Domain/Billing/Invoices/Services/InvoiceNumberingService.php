<?php

namespace App\Domain\Billing\Invoices\Services;

use App\Models\Invoice;

class InvoiceNumberingService
{
    public function generate(int $organizationId): string
    {
        $year = date('Y');

        $lastInvoice = Invoice::where('organization_id', $organizationId)
            ->where('number', 'like', $year . '-%')
            ->orderBy('number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->number, 5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $year . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
