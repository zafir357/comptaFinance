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
     * TABLE: reconciliations (rapprochements bancaires)
     * 
     * Lie une transaction bancaire avec une facture OU une dépense.
     * C'est une relation POLYMORPHIQUE!
     * 
     * Relations SQL:
     * - MANY reconciliations belong to ONE bank_transaction = MANY-TO-ONE (belongsTo)
     * - ONE reconciliation can link to Invoice OR Expense = POLYMORPHIC (morphTo)
     * 
     * POLYMORPHIC RELATION - Comment ça marche?
     * 
     * Exemple 1:
     * bank_transaction_id = 1 (virement de +2000€)
     * reconcilable_type = "App\Models\Invoice"
     * reconcilable_id = 5 (facture #2026-0001)
     * → Cette transaction correspond au paiement de la facture #2026-0001
     * 
     * Exemple 2:
     * bank_transaction_id = 2 (prélèvement de -150€)
     * reconcilable_type = "App\Models\Expense"
     * reconcilable_id = 8 (dépense carburant)
     * → Cette transaction correspond à la dépense de carburant
     * 
     * morphs('reconcilable') crée automatiquement:
     * - reconcilable_type (string)
     * - reconcilable_id (bigint)
     */
    public function up(): void
    {
        Schema::create('reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_transaction_id')->constrained()->cascadeOnDelete();
            $table->morphs('reconcilable');  // Crée reconcilable_type + reconcilable_id
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconciliations');
    }
};
