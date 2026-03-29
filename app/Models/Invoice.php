<?php

namespace App\Models;

use App\Support\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * MODEL: Invoice (facture)
 *
 * Une facture appartient à UNE organization et UN customer.
 * Une facture a plusieurs invoice_lines (lignes de facturation).
 * Une facture peut avoir UNE reconciliation (rapprochement bancaire).
 *
 * RELATIONS SQL:
 * - MANY-TO-ONE avec Organization = belongsTo() [from trait]
 * - MANY-TO-ONE avec Customer = belongsTo()
 * - ONE-TO-MANY avec InvoiceLine = hasMany()
 * - ONE-TO-MANY avec Reconciliation via POLYMORPHIC = morphMany() ← PAIEMENTS PARTIELS!
 *
 * MONEY HANDLING:
 * Tous les montants sont en CENTIMES (integer) pour éviter les erreurs de précision.
 * On utilise des ACCESSORS pour convertir en euros pour l'affichage.
 */
class Invoice extends Model
{
    use BelongsToOrganization;

    protected $guarded = [];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // MANY-TO-ONE: Plusieurs invoices appartiennent à un customer
    // SQL: SELECT * FROM customers WHERE id = ?
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // ONE-TO-MANY: Une invoice a plusieurs lines
    // SQL: SELECT * FROM invoice_lines WHERE invoice_id = ?
    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    // ONE-TO-MANY POLYMORPHIC: Une invoice peut avoir PLUSIEURS reconciliations (paiements partiels!)
    //
    // Exemple: Facture de 1000€ payée en 2x:
    // - reconciliation 1: +500€ (transaction bancaire #5)
    // - reconciliation 2: +500€ (transaction bancaire #8)
    //
    // SQL: SELECT * FROM reconciliations
    //      WHERE reconcilable_type = 'App\Models\Invoice'
    //      AND reconcilable_id = ?
    public function reconciliations(): MorphMany
    {
        return $this->morphMany(Reconciliation::class, 'reconcilable');
    }

    // ACCESSOR: Convertir centimes → euros pour affichage
    // Usage: $invoice->total_in_euros → "19.99"
    public function getTotalInEurosAttribute(): string
    {
        return number_format($this->total / 100, 2);
    }

    public function getSubtotalInEurosAttribute(): string
    {
        return number_format($this->subtotal / 100, 2);
    }

    public function getVatTotalInEurosAttribute(): string
    {
        return number_format($this->vat_total / 100, 2);
    }

    // HELPER: Check si facture est totalement payée (toutes les reconciliations)
    public function isPaid(): bool
    {
        if ($this->status === 'paid') {
            return true;
        }

        // Vérifier si le total des rapprochements = total facture
        $reconciledAmount = $this->reconciliations()->sum('amount');
        return $reconciledAmount >= $this->total;
    }

    // HELPER: Montant déjà rapproché
    public function getReconciledAmountAttribute(): int
    {
        return $this->reconciliations()->sum('amount');
    }

    // HELPER: Montant restant à rapprocher
    public function getRemainingAmountAttribute(): int
    {
        return $this->total - $this->reconciled_amount;
    }

    // HELPER: Check si facture est en retard
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date->isPast();
    }
}
