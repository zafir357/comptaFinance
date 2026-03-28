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
     * TABLE: memberships (table PIVOT pour many-to-many)
     * 
     * Lie les Users aux Organizations avec un rôle.
     * C'est une table PIVOT avec données supplémentaires (rôle).
     * 
     * Relations SQL:
     * - MANY users can belong to MANY organizations = MANY-TO-MANY
     * - Cette table stocke la relation + le rôle de chaque user dans chaque org
     * 
     * Exemple:
     * User "Jean" → Organization "Cabinet Dupont" → role: "owner"
     * User "Jean" → Organization "Cabinet Martin" → role: "accountant"
     * User "Marie" → Organization "Cabinet Dupont" → role: "viewer"
     */
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['owner', 'accountant', 'viewer'])->default('viewer');
            $table->timestamps();
            
            // Un user ne peut être qu'une seule fois dans une org
            $table->unique(['user_id', 'organization_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
