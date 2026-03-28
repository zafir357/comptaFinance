<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TABLE: ticket_messages (messages des tickets)
     * 
     * Système de chat pour chaque ticket.
     * Permet conversation entre user et support.
     * 
     * Relations SQL:
     * - MANY ticket_messages belong to ONE ticket = MANY-TO-ONE (belongsTo)
     * - MANY ticket_messages belong to ONE user (auteur) = MANY-TO-ONE (belongsTo)
     * - ONE ticket has MANY ticket_messages = ONE-TO-MANY (hasMany)
     */
    public function up(): void
    {
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');                                 // Contenu du message
            $table->boolean('is_internal')->default(false);      // Message interne (non visible client)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};
