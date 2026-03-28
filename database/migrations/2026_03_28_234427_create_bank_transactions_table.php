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
     * TABLE: bank_transactions (transactions bancaires)
     * 
     * Importées depuis un fichier CSV de la banque.
     * Peuvent être rapprochées avec des factures ou dépenses.
     * 
     * Relations SQL:
     * - MANY bank_transactions belong to ONE organization = MANY-TO-ONE (belongsTo)
     * - ONE bank_transaction can have ONE reconciliation = ONE-TO-ONE (hasOne)
     * 
     * IMPORTANT - external_id:
     * C'est l'ID unique de la banque. Permet l'idempotence:
     * Si tu importes 2 fois le même CSV, ça ne crée pas de doublons!
     * 
     * unique(['organization_id', 'external_id']) = 
     * "Cette transaction bancaire existe déjà pour cette org, skip!"
     */
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->date('date');                                 // Date de transaction
            $table->string('description');                        // Ex: "VIREMENT SARL TECHNOWEB"
            $table->integer('amount');                            // Montant en centimes (+ ou -)
            $table->string('currency', 3)->default('EUR');       // EUR, USD, etc.
            $table->string('external_id');                        // ID unique de la banque
            $table->boolean('reconciled')->default(false);       // Déjà rapproché?
            $table->timestamps();
            
            // CRITIQUE pour l'idempotence des imports
            $table->unique(['organization_id', 'external_id']);
            
            // Index pour afficher rapidement les transactions non rapprochées
            $table->index(['organization_id', 'reconciled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
