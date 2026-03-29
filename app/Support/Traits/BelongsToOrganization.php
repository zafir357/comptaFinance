<?php

namespace App\Support\Traits;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * TRAIT: BelongsToOrganization
 *
 * Add to any model that belongs to an organization.
 * Automatically scopes queries to current organization.
 *
 * Usage:
 *   class Invoice extends Model {
 *       use BelongsToOrganization;
 *   }
 *
 * Then:
 *   Invoice::all() → only invoices for current_org
 *   $invoice->organization → belongs_to relationship
 */
trait BelongsToOrganization
{
    protected static function bootBelongsToOrganization(): void
    {
        // Automatically add organization_id to all queries
        static::addGlobalScope('organization', function (Builder $query) {
            $orgId = app(\App\Support\Tenancy\CurrentOrganization::class)->id();

            if ($orgId) {
                $query->where('organization_id', $orgId);
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Create a new instance of the model.
     * Automatically set organization_id.
     */
    public static function make(array $attributes = []): static
    {
        $attributes['organization_id'] ??= app(\App\Support\Tenancy\CurrentOrganization::class)->id();
        return parent::make($attributes);
    }
}
