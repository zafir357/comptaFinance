<?php

namespace App\Repositories;

use App\Support\Tenancy\CurrentOrganization;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * ABSTRACT: BaseRepository
 *
 * Base class for all repositories.
 * Automatically applies organization scoping to all queries.
 *
 * Child repositories only need to implement:
 *   protected Model $model;
 *   protected function getModel(): Model;
 *
 * Usage:
 *   class InvoiceRepository extends BaseRepository {
 *       protected Model $model = Invoice::class;
 *   }
 *
 *   $repo = app(InvoiceRepository::class);
 *   $repo->all();  // → Invoices for current org only
 */
abstract class BaseRepository
{
    protected Model $model;

    abstract protected function getModel(): Model;

    /**
     * Get all records for current organization.
     */
    public function all(): Collection
    {
        return $this->getModel()->all();
    }

    /**
     * Get paginated records for current organization.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->getModel()->paginate($perPage);
    }

    /**
     * Find a record by ID (automatically scoped to org).
     */
    public function find(int $id): ?Model
    {
        return $this->getModel()->find($id);
    }

    /**
     * Find or fail by ID.
     */
    public function findOrFail(int $id): Model
    {
        return $this->getModel()->findOrFail($id);
    }

    /**
     * Create a record with organization_id.
     */
    public function create(array $data): Model
    {
        $data['organization_id'] ??= app(CurrentOrganization::class)->id();
        return $this->getModel()->create($data);
    }

    /**
     * Update a record.
     */
    public function update(Model $model, array $data): Model
    {
        $model->update($data);
        return $model->refresh();
    }

    /**
     * Delete a record.
     */
    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * Get the query builder for additional filtering.
     */
    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->getModel()->query();
    }

    /**
     * Get the current organization ID.
     */
    protected function currentOrganizationId(): ?int
    {
        return app(CurrentOrganization::class)->id();
    }
}
