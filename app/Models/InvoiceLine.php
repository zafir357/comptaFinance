<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MODEL: InvoiceLine (ligne de facturation)
 * 
 * Une ligne = un produit/service dans une facture.
 * Appartient à UNE invoice.
 * 
 * RELATIONS SQL:
 * - MANY-TO-ONE avec Invoice = belongsTo()
 * 
 * Exemple:
 * Facture #2026-0001
 *   → Ligne 1: "Développement site" x 5 jours @ 400€/jour = 2000€ + 20% TVA
 *   → Ligne 2: "Hébergement" x 1 @ 300€ = 300€ + 20% TVA
 */
class InvoiceLine extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'integer',
        'vat_rate' => 'decimal:2',
        'total' => 'integer',
    ];
    
    // MANY-TO-ONE: Plusieurs lines appartiennent à une invoice
    // SQL: SELECT * FROM invoices WHERE id = ?
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
    
    // ACCESSOR: Convertir centimes → euros
    public function getUnitPriceInEurosAttribute(): string
    {
        return number_format($this->unit_price / 100, 2);
    }
    
    public function getTotalInEurosAttribute(): string
    {
        return number_format($this->total / 100, 2);
    }
}
