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
     * TABLE: expenses (notes de frais / dépenses)
     * 
     * Chaque dépense appartient à une organization.
     * Peut avoir un justificatif (reçu) uploadé.
     * 
     * Relations SQL:
     * - MANY expenses belong to ONE organization = MANY-TO-ONE (belongsTo)
     * - ONE organization has MANY expenses = ONE-TO-MANY (hasMany)
     * - ONE expense can have ONE reconciliation = ONE-TO-ONE via polymorphic (morphOne)
     * 
     * Le receipt_status permet de tracker le traitement async:
     * - uploaded: fichier uploadé
     * - processing: job en cours (extraction données)
     * - processed: traitement terminé
     * - failed: erreur
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('category');                           // Ex: "carburant", "fournitures"
            $table->string('supplier');                           // Ex: "Total", "Bureau Vallée"
            $table->date('date');                                 // Date de la dépense
            $table->integer('amount');                            // Montant HT en centimes
            $table->integer('vat_amount')->default(0);           // TVA en centimes
            $table->string('receipt_path')->nullable();           // Chemin vers le fichier
            $table->enum('receipt_status', ['uploaded', 'processing', 'processed', 'failed'])->default('uploaded');
            $table->timestamp('receipt_processed_at')->nullable();
            $table->timestamps();
            
            // Index pour filtrer par catégorie (utile pour rapports)
            $table->index(['organization_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
