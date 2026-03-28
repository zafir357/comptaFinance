<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * MODEL: Reconciliation (rapprochement bancaire)
 * 
 * Lie une BankTransaction avec une Invoice OU une Expense.
 * C'est une relation POLYMORPHIQUE!
 * 
 * SUPPORT DES PAIEMENTS PARTIELS:
 * Le champ 'amount' permet de tracer combien de la transaction bancaire
 * est alloué à cette facture/dépense.
 * 
 * RELATIONS SQL:
 * - MANY-TO-ONE avec BankTransaction = belongsTo()
 * - POLYMORPHIC avec Invoice OU Expense = morphTo()
 * 
 * POLYMORPHIC - Exemple avec paiement partiel:
 * 
 * Facture #2026-0001 (total: 100000 centimes = 1000€)
 * 
 * Rapprochement 1:
 *   bank_transaction_id = 5 (+500€)
 *   reconcilable_type = 'App\Models\Invoice'
 *   reconcilable_id = 1
 *   amount = 50000 (500€)
 * 
 * Rapprochement 2:
 *   bank_transaction_id = 8 (+500€)
 *   reconcilable_type = 'App\Models\Invoice'
 *   reconcilable_id = 1 (même facture!)
 *   amount = 50000 (500€)
 * 
 * Total rapproché: 50000 + 50000 = 100000 ✅ Facture totalement payée!
 */
class Reconciliation extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'amount' => 'integer',
    ];
    
    // MANY-TO-ONE: Plusieurs reconciliations appartiennent à une bank_transaction
    // SQL: SELECT * FROM bank_transactions WHERE id = ?
    public function bankTransaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class);
    }
    
    // POLYMORPHIC: Peut pointer vers Invoice OU Expense
    // Laravel va automatiquement:
    // 1. Lire reconcilable_type pour savoir quel model charger
    // 2. Lire reconcilable_id pour l'ID de ce model
    // 3. Faire la requête appropriée
    // 
    // Si reconcilable_type = 'App\Models\Invoice' et reconcilable_id = 10:
    // SQL: SELECT * FROM invoices WHERE id = 10
    // 
    // Si reconcilable_type = 'App\Models\Expense' et reconcilable_id = 3:
    // SQL: SELECT * FROM expenses WHERE id = 3
    public function reconcilable(): MorphTo
    {
        return $this->morphTo();
    }
    
    // ACCESSOR: Convertir centimes → euros
    public function getAmountInEurosAttribute(): string
    {
        return number_format($this->amount / 100, 2);
    }
}
