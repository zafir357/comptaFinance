<?php

namespace App\Models;

use App\Support\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * MODEL: Expense (note de frais / dépense)
 *
 * Une dépense appartient à UNE organization.
 * Peut avoir un justificatif (reçu) uploadé.
 * Peut être rapprochée avec une transaction bancaire.
 *
 * RELATIONS SQL:
 * - MANY-TO-ONE avec Organization = belongsTo() [from trait]
 * - ONE-TO-MANY avec Reconciliation via POLYMORPHIC = morphMany()
 */
class Expense extends Model
{
    use BelongsToOrganization;

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'receipt_processed_at' => 'datetime',
    ];

    // ONE-TO-MANY POLYMORPHIC: Une expense peut avoir PLUSIEURS reconciliations
    // (rare pour une dépense, mais possible si payée en plusieurs fois)
    //
    // SQL: SELECT * FROM reconciliations
    //      WHERE reconcilable_type = 'App\Models\Expense'
    //      AND reconcilable_id = ?
    public function reconciliations(): MorphMany
    {
        return $this->morphMany(Reconciliation::class, 'reconcilable');
    }

    // ACCESSOR: Convertir centimes → euros
    public function getAmountInEurosAttribute(): string
    {
        return number_format($this->amount / 100, 2);
    }

    public function getVatAmountInEurosAttribute(): string
    {
        return number_format($this->vat_amount / 100, 2);
    }

    public function getTotalInEurosAttribute(): string
    {
        return number_format(($this->amount + $this->vat_amount) / 100, 2);
    }

    // HELPER: Check si justificatif est traité
    public function isReceiptProcessed(): bool
    {
        return $this->receipt_status === 'processed';
    }
}

