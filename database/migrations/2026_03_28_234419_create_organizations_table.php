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
     * TABLE: organizations (cabinets comptables)
     * 
     * C'est la table CENTRALE du multi-tenant.
     * Chaque organisation = un cabinet comptable indépendant.
     * 
     * Relations:
     * - ONE organization has MANY users (via memberships) = many-to-many
     * - ONE organization has MANY invoices = one-to-many
     * - ONE organization has MANY customers = one-to-many
     */
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // Ex: "Cabinet Dupont"
            $table->string('slug')->unique();          // Ex: "cabinet-dupont" (pour URL)
            $table->json('settings')->nullable();      // Paramètres futurs (logo, TVA, etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
