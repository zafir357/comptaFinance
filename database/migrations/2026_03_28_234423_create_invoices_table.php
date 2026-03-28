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
     * TABLE: invoices (factures)
     * 
     * Chaque facture appartient à UNE organization et UN customer.
     * Stocke les montants en CENTIMES (integer) pour éviter les erreurs de précision.
     * 
     * Relations SQL:
     * - MANY invoices belong to ONE organization = MANY-TO-ONE (belongsTo)
     * - MANY invoices belong to ONE customer = MANY-TO-ONE (belongsTo)
     * - ONE invoice has MANY invoice_lines = ONE-TO-MANY (hasMany)
     * - ONE invoice can have ONE reconciliation = ONE-TO-ONE via polymorphic (morphOne)
     * 
     * Pourquoi integer pour l'argent ?
     * - Float/decimal = erreurs de précision (ex: 0.1 + 0.2 ≠ 0.3)
     * - Integer = toujours précis (ex: 1999 centimes = 19.99€)
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('number');                                   // Ex: "2026-0001"
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue'])->default('draft');
            $table->date('issue_date');                                 // Date d'émission
            $table->date('due_date');                                   // Date d'échéance
            $table->integer('subtotal')->default(0);                    // Total HT en centimes
            $table->integer('vat_total')->default(0);                   // Total TVA en centimes
            $table->integer('total')->default(0);                       // Total TTC en centimes
            $table->text('notes')->nullable();                          // Notes/conditions
            $table->timestamp('sent_at')->nullable();                   // Quand envoyée
            $table->timestamp('paid_at')->nullable();                   // Quand payée
            $table->timestamps();
            
            // CONTRAINTES IMPORTANTES:
            // - Numéro unique par organisation (2 orgs peuvent avoir "2026-0001")
            $table->unique(['organization_id', 'number']);
            
            // - Index pour filtrer rapidement par status (impayées, brouillons, etc.)
            $table->index(['organization_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
