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
     * TABLE: customers (clients des cabinets)
     * 
     * Chaque organization a ses propres clients.
     * 
     * Relations SQL:
     * - MANY customers belong to ONE organization = MANY-TO-ONE (belongsTo)
     * - ONE organization has MANY customers = ONE-TO-MANY (hasMany)
     * - ONE customer has MANY invoices = ONE-TO-MANY (hasMany)
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');                    // Ex: "SARL TechnoWeb"
            $table->string('email')->nullable();       // Ex: "contact@technoweb.fr"
            $table->string('phone')->nullable();
            $table->text('address')->nullable();       // Adresse complète
            $table->string('tax_number')->nullable();  // SIRET/SIREN
            $table->timestamps();
            
            // Index pour rechercher rapidement les clients d'une org
            $table->index(['organization_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
