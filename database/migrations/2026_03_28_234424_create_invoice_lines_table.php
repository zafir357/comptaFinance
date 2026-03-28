<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * TABLE: invoice_lines (lignes de facturation)
     * 
     * Une facture contient plusieurs lignes (produits/services).
     * 
     * Relations SQL:
     * - MANY invoice_lines belong to ONE invoice = MANY-TO-ONE (belongsTo)
     * - ONE invoice has MANY invoice_lines = ONE-TO-MANY (hasMany)
     * 
     * Exemple:
     * Facture #2026-0001
     *   → Ligne 1: "Développement site web" x 1 @ 2000€
     *   → Ligne 2: "Hébergement annuel" x 1 @ 300€
     */
    public function up(): void
    {
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');                         // Ex: "Développement site web"
            $table->decimal('quantity', 10, 2)->default(1);       // Ex: 1.5 jours
            $table->integer('unit_price');                        // Prix unitaire en centimes
            $table->decimal('vat_rate', 5, 2)->default(20.00);   // Taux TVA (20%)
            $table->integer('total');                             // Total ligne en centimes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
    }
};
