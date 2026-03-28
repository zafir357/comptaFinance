<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODEL: Customer (client d'un cabinet)
 * 
 * Chaque customer appartient à UNE organization.
 * Un customer peut avoir plusieurs invoices.
 * 
 * RELATIONS SQL:
 * - MANY-TO-ONE avec Organization = belongsTo()
 * - ONE-TO-MANY avec Invoice = hasMany()
 */
class Customer extends Model
{
    protected $guarded = [];
    
    // MANY-TO-ONE: Plusieurs customers appartiennent à une organization
    // SQL: SELECT * FROM organizations WHERE id = ?
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
    
    // ONE-TO-MANY: Un customer a plusieurs invoices
    // SQL: SELECT * FROM invoices WHERE customer_id = ?
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
    
    // SCOPE: Filtrer par organization (pour sécurité multi-tenant)
    // Usage: Customer::forOrganization($orgId)->get()
    // SQL: SELECT * FROM customers WHERE organization_id = ?
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
}
