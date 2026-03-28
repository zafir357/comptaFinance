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
     * TABLE: tickets (support client)
     * 
     * Système de tickets pour montrer que tu comprends le support utilisateur.
     * Important pour le job: "Gestion des tickets techniques des clients"
     * 
     * Relations SQL:
     * - MANY tickets belong to ONE organization = MANY-TO-ONE (belongsTo)
     * - MANY tickets belong to ONE user (créateur) = MANY-TO-ONE (belongsTo)
     * - ONE ticket has MANY ticket_messages = ONE-TO-MANY (hasMany)
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject');                                   // Ex: "Erreur lors de l'export PDF"
            $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->json('tags')->nullable();                            // Ex: ["bug", "facturation"]
            $table->timestamps();
            
            // Index pour filtrer par status (tickets ouverts, fermés)
            $table->index(['organization_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
