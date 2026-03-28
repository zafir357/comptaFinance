<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * MODEL: BankTransaction (transaction bancaire)
 * 
 * Importée depuis un CSV de la banque.
 * Peut être rapprochée avec une Invoice OU une Expense.
 * 
 * RELATIONS SQL:
 * - MANY-TO-ONE avec Organization = belongsTo()
 * - ONE-TO-ONE avec Reconciliation = hasOne()
 * 
 * IDEMPOTENCE:
 * external_id = identifiant unique de la banque
 * Permet d'importer le même CSV plusieurs fois sans créer de doublons
 * (grâce au unique constraint dans la migration)
 */
class BankTransaction extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'date' => 'date',
        'reconciled' => 'boolean',
    ];
    
    // MANY-TO-ONE: Plusieurs transactions appartiennent à une organization
    // SQL: SELECT * FROM organizations WHERE id = ?
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
    
    // ONE-TO-ONE: Une transaction peut avoir une reconciliation
    // SQL: SELECT * FROM reconciliations WHERE bank_transaction_id = ?
    public function reconciliation(): HasOne
    {
        return $this->hasOne(Reconciliation::class);
    }
    
    // SCOPE: Filtrer par organization
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
    
    // SCOPE: Seulement les transactions non rapprochées
    // Usage: BankTransaction::unreconciled()->get()
    public function scopeUnreconciled($query)
    {
        return $query->where('reconciled', false);
    }
    
    // ACCESSOR: Convertir centimes → euros
    public function getAmountInEurosAttribute(): string
    {
        return number_format($this->amount / 100, 2);
    }
    
    // HELPER: Check si c'est un crédit (entrée d'argent)
    public function isCredit(): bool
    {
        return $this->amount > 0;
    }
    
    // HELPER: Check si c'est un débit (sortie d'argent)
    public function isDebit(): bool
    {
        return $this->amount < 0;
    }
}
