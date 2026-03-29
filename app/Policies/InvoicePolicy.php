<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

/**
 * POLICY: InvoicePolicy
 *
 * Handles authorization for invoice operations.
 * Checks:
 * - User is member of the invoice's organization
 * - User has appropriate role
 */
class InvoicePolicy
{
    /**
     * Determine if user can view invoices (index).
     */
    public function viewAny(User $user): bool
    {
        return true; // Scoped by middleware
    }

    /**
     * Determine if user can view this invoice.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        return $user->belongsToOrganization($invoice->organization_id);
    }

    /**
     * Determine if user can create invoices.
     */
    public function create(User $user): bool
    {
        return true; // Scoped by middleware
    }

    /**
     * Determine if user can update this invoice.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        // Only allow if user is in organization
        if (!$user->belongsToOrganization($invoice->organization_id)) {
            return false;
        }

        // Only allow update on draft invoices
        return $invoice->status === 'draft' && $user->canEditIn($invoice->organization_id);
    }

    /**
     * Determine if user can delete this invoice.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        // Only allow deletion of draft invoices by owner
        return $invoice->status === 'draft'
            && $user->isOwnerOf($invoice->organization_id);
    }
}
