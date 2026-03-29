<?php

namespace App\Domain\Billing\Invoices\Repositories;

use App\Models\Invoice;
use App\Repositories\BaseRepository;

/**
 * REPOSITORY: InvoiceRepository
 *
 * Data access abstraction for invoices.
 * All queries automatically scoped to current organization via BaseRepository.
 *
 * Usage:
 *   $repo = app(InvoiceRepository::class);
 *   $repo->all();              // All invoices for current org
 *   $repo->findOrFail($id);    // Find by ID (scoped to org)
 *   $repo->byStatus('paid');   // Custom query
 */
class InvoiceRepository extends BaseRepository
{
    protected Invoice $model;

    protected function getModel(): Invoice
    {
        return new Invoice();
    }

    /**
     * Get invoices filtered by status.
     */
    public function byStatus(string $status)
    {
        return $this->query()->where('status', $status);
    }

    /**
     * Get overdue invoices.
     */
    public function overdue()
    {
        return $this->query()
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->orderBy('due_date', 'asc');
    }

    /**
     * Get unpaid invoices.
     */
    public function unpaid()
    {
        return $this->query()
            ->where('status', '!=', 'paid')
            ->orderBy('due_date', 'asc');
    }

    /**
     * Get invoices for a specific customer.
     */
    public function forCustomer(int $customerId)
    {
        return $this->query()->where('customer_id', $customerId);
    }

    /**
     * Get recent invoices (for dashboard).
     */
    public function recent(int $limit = 10)
    {
        return $this->query()
            ->with('customer')
            ->latest('created_at')
            ->limit($limit);
    }

    /**
     * Get total amount for a status.
     */
    public function totalByStatus(string $status): int
    {
        return $this->query()
            ->where('status', $status)
            ->sum('total');
    }
}
