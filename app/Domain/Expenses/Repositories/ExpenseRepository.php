<?php

namespace App\Domain\Expenses\Repositories;

use App\Models\Expense;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * REPOSITORY: ExpenseRepository
 *
 * Data access abstraction for expenses.
 * All queries automatically scoped to current organization via BaseRepository.
 *
 * Usage:
 *   $repo = app(ExpenseRepository::class);
 *   $repo->all();                      // All expenses for current org
 *   $repo->findOrFail($id);            // Find by ID (scoped to org)
 *   $repo->byCategory('travel');       // Custom query
 *   $repo->byReceiptStatus('pending'); // Filter by receipt status
 */
class ExpenseRepository extends BaseRepository
{
    protected function getModel(): Model
    {
        return new Expense();
    }

    /**
     * Get expenses filtered by category.
     */
    public function byCategory(string $category)
    {
        return $this->query()->where('category', $category);
    }

    /**
     * Get expenses filtered by receipt status.
     */
    public function byReceiptStatus(?string $status)
    {
        if ($status === null) {
            return $this->query()->whereNull('receipt_status');
        }

        return $this->query()->where('receipt_status', $status);
    }

    /**
     * Get expenses with pending receipts (awaiting processing).
     */
    public function pendingReceipts()
    {
        return $this->query()
            ->where('receipt_status', 'pending')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Get expenses for a date range.
     */
    public function between(\DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        return $this->query()
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc');
    }

    /**
     * Get expenses for a specific supplier.
     */
    public function forSupplier(string $supplier)
    {
        return $this->query()->where('supplier', $supplier);
    }

    /**
     * Get recent expenses (for dashboard).
     */
    public function recent(int $limit = 10)
    {
        return $this->query()
            ->latest('date')
            ->limit($limit);
    }

    /**
     * Get total amount by category.
     */
    public function totalByCategory(): array
    {
        return $this->query()
            ->selectRaw('category, SUM(amount + vat_amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();
    }

    /**
     * Get total expenses for a date range.
     */
    public function totalBetween(\DateTimeInterface $startDate, \DateTimeInterface $endDate): int
    {
        return (int) $this->query()
            ->whereBetween('date', [$startDate, $endDate])
            ->sum(\DB::raw('amount + vat_amount'));
    }

    /**
     * Get failed receipts (that need manual attention).
     */
    public function failedReceipts()
    {
        return $this->query()
            ->where('receipt_status', 'failed')
            ->orderBy('created_at', 'asc');
    }
}
