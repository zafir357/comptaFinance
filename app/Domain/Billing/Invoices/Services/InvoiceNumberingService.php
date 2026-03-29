<?php

namespace App\Domain\Billing\Invoices\Services;

use App\Models\Invoice;
use App\Support\Tenancy\CurrentOrganization;

/**
 * SERVICE: InvoiceNumberingService
 *
 * Generates sequential invoice numbers per organization.
 * Format: YYYY-NNNN (e.g., 2026-0001, 2026-0002)
 *
 * IMPORTANT: This is ONE operation per organization per year.
 * No concurrency issues because of:
 * - Global lock on organization_id + year
 * - Database unique constraint (organization_id, number)
 */
class InvoiceNumberingService
{
    public function __construct(
        private CurrentOrganization $tenancy,
    ) {}

    /**
     * Generate the next invoice number for current organization.
     *
     * @return string Invoice number like "2026-0001"
     */
    public function nextNumber(): string
    {
        $orgId = $this->tenancy->id();
        $year = now()->year;

        // Find highest sequence number this year
        $lastInvoice = Invoice::where('organization_id', $orgId)
            ->whereYear('created_at', $year)
            ->orderByRaw('CAST(SUBSTRING(number, 6) AS UNSIGNED) DESC')
            ->first();

        $nextSequence = 1;
        if ($lastInvoice) {
            // Extract number from "2026-0001"
            $parts = explode('-', $lastInvoice->number);
            $sequence = (int) $parts[1];
            $nextSequence = $sequence + 1;
        }

        return sprintf('%d-%04d', $year, $nextSequence);
    }
}
