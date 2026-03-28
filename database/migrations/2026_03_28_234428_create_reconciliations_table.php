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
     * IMPORTANT - PAIEMENTS PARTIELS:
     * Une facture de 1000€ peut être payée en plusieurs fois:
     *   - Transaction bancaire 1: +500€ → reconciliation 1
     *   - Transaction bancaire 2: +500€ → reconciliation 2
     * 
     * Donc:
     * - UNE Invoice peut avoir PLUSIEURS reconciliations (morphMany)
     * - UNE BankTransaction peut avoir UNE SEULE reconciliation (hasOne)
     * - On stocke l'amount pour tracer combien est alloué
     * 
     * Relations SQL:
     * - MANY reconciliations belong to ONE bank_transaction = MANY-TO-ONE (belongsTo)
     * - MANY reconciliations can link to ONE Invoice/Expense = POLYMORPHIC (morphTo)
     * 
     * Exemple:
     * Facture #2026-0001 (1000€) payée en 2 fois:
     * 
     * reconciliation #1:
     *   bank_transaction_id = 5 (+500€)
     *   reconcilable_type = "App\Models\Invoice"
     *   reconcilable_id = 1 (facture #2026-0001)
     *   amount = 50000 (500€ en centimes)
     * 
     * reconciliation #2:
     *   bank_transaction_id = 8 (+500€)
     *   reconcilable_type = "App\Models\Invoice"
     *   reconcilable_id = 1 (même facture!)
     *   amount = 50000 (500€ en centimes)
     */
    public function up(): void
    {
        Schema::create('reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_transaction_id')->constrained()->cascadeOnDelete();
            $table->morphs('reconcilable');          // Crée reconcilable_type + reconcilable_id
            $table->integer('amount');               // Montant alloué en centimes (important pour paiements partiels!)
            $table->text('notes')->nullable();       // Notes sur le rapprochement
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
