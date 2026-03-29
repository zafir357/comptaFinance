<?php

namespace App\Domain\Banking\Repositories;

use App\Models\BankTransaction;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * REPOSITORY: BankTransactionRepository
 *
 * Repository for bank transaction persistence.
 * Extends BaseRepository for automatic organization scoping.
 *
 * All queries are automatically scoped to the current organization
 * via the BelongsToOrganization trait on the BankTransaction model.
 *
 * Usage:
 *   $repo = app(BankTransactionRepository::class);
 *   $repo->unreconciled()->paginate();
 */
class BankTransactionRepository extends BaseRepository
{
    protected Model $model;

    protected function getModel(): Model
    {
        return app(BankTransaction::class);
    }

    /**
     * Get all unreconciled transactions for current organization
     */
    public function unreconciled()
    {
        return $this->query()->where('reconciled', false);
    }

    /**
     * Get paginated unreconciled transactions
     */
    public function unreconciledPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->unreconciled()->paginate($perPage);
    }

    /**
     * Find transaction by external_id
     */
    public function findByExternalId(string $externalId): ?BankTransaction
    {
        return $this->query()
            ->where('external_id', $externalId)
            ->first();
    }

    /**
     * Get transactions within date range
     */
    public function byDateRange($startDate, $endDate)
    {
        return $this->query()
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc');
    }

    /**
     * Get paginated transactions within date range
     */
    public function byDateRangePaginated($startDate, $endDate, int $perPage = 15): LengthAwarePaginator
    {
        return $this->byDateRange($startDate, $endDate)->paginate($perPage);
    }

    /**
     * Get only credit transactions
     */
    public function credits()
    {
        return $this->query()->where('amount', '>', 0);
    }

    /**
     * Get only debit transactions
     */
    public function debits()
    {
        return $this->query()->where('amount', '<', 0);
    }

    /**
     * Get transactions with reconciliations eager loaded
     */
    public function withReconciliations()
    {
        return $this->query()->with('reconciliation');
    }

    /**
     * Search transactions by description
     */
    public function search(string $query)
    {
        return $this->query()
            ->where('description', 'like', "%{$query}%")
            ->orWhere('external_id', 'like', "%{$query}%");
    }
}
