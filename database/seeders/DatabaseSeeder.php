<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organization;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Crée des données de test pour le développement:
     * - 1 organization (Cabinet Démo)
     * - 1 user (demo@comptafinance.test)
     * - Le user est owner de l'organization
     */
    public function run(): void
    {
        // Créer l'organization
        $organization = Organization::create([
            'name' => 'Cabinet Démo SARL',
            'slug' => 'cabinet-demo',
            'settings' => [
                'currency' => 'EUR',
                'default_vat_rate' => 20.00,
            ],
        ]);

        // Créer l'utilisateur
        $user = User::create([
            'name' => 'Jean Dupont',
            'email' => 'demo@comptafinance.test',
            'password' => bcrypt('password'),  // Mot de passe: password
            'email_verified_at' => now(),
        ]);

        // Lier l'user à l'organization comme owner
        \App\Models\Membership::create([
            'user_id' => $user->id,
            'organization_id' => $organization->id,
            'role' => 'owner',
        ]);

        echo "✅ Seeder terminé!\n";
        echo "📧 Email: demo@comptafinance.test\n";
        echo "🔑 Password: password\n";
        echo "🏢 Organization: {$organization->name}\n";
    }
}
