<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODEL: Organization (cabinet comptable)
 * 
 * Une organization = un cabinet comptable = un tenant.
 * Toutes les données (factures, clients, etc.) sont isolées par organization_id.
 * 
 * RELATIONS SQL:
 * - MANY-TO-MANY avec User (via memberships) = belongsToMany()
 * - ONE-TO-MANY avec Customer = hasMany()
 * - ONE-TO-MANY avec Invoice = hasMany()
 * - ONE-TO-MANY avec Expense = hasMany()
 * - ONE-TO-MANY avec BankTransaction = hasMany()
 * - ONE-TO-MANY avec Ticket = hasMany()
 */
class Organization extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'settings' => 'array',  // JSON → array PHP automatiquement
    ];
    
    // MANY-TO-MANY: Une org a plusieurs users (via table pivot memberships)
    // SQL: SELECT users.* FROM users 
    //      INNER JOIN memberships ON users.id = memberships.user_id 
    //      WHERE memberships.organization_id = ?
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'memberships')
            ->withPivot('role')      // Accès au rôle: $user->pivot->role
            ->withTimestamps();
    }
    
    // ONE-TO-MANY: Une org a plusieurs memberships
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }
    
    // ONE-TO-MANY: Une org a plusieurs customers
    // SQL: SELECT * FROM customers WHERE organization_id = ?
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
    
    // ONE-TO-MANY: Une org a plusieurs invoices
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
    
    // ONE-TO-MANY: Une org a plusieurs expenses
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
    
    // ONE-TO-MANY: Une org a plusieurs bank_transactions
    public function bankTransactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }
    
    // ONE-TO-MANY: Une org a plusieurs tickets
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
