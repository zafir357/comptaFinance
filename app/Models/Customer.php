<?php

namespace App\Models;

use App\Support\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODEL: Customer (client d'un cabinet)
 *
 * Chaque customer appartient à UNE organization.
 * Un customer peut avoir plusieurs invoices.
 *
 * RELATIONS SQL:
 * - MANY-TO-ONE avec Organization = belongsTo() [from trait]
 * - ONE-TO-MANY avec Invoice = hasMany()
 */
class Customer extends Model
{
    use BelongsToOrganization;

    protected $guarded = [];

    // ONE-TO-MANY: Un customer a plusieurs invoices
    // SQL: SELECT * FROM invoices WHERE customer_id = ?
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}

