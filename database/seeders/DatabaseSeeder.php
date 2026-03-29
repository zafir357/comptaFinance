<?php

namespace Database\Seeders;

use App\Domain\Billing\Invoices\Actions\CreateInvoiceAction;
use App\Domain\Billing\Invoices\Data\InvoiceData;
use App\Domain\Billing\Invoices\Data\InvoiceLineData;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;
use App\Support\Tenancy\CurrentOrganization;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo user
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@comptafinance.test',
            'password' => bcrypt('password'),
        ]);

        // Create demo organization
        $org = Organization::create([
            'name' => 'Compta Démo SARL',
            'slug' => 'compta-demo',
        ]);

        // Add user to organization as owner
        $org->memberships()->create([
            'user_id' => $user->id,
            'role' => 'owner',
        ]);

        // Set organization context
        app(CurrentOrganization::class)->set($org);

        // Create demo customers
        $customers = [];
        $customerNames = [
            ['name' => 'TechCorp France', 'email' => 'contact@techcorp.fr'],
            ['name' => 'Consulting Plus', 'email' => 'info@consultingplus.fr'],
            ['name' => 'Marketing Digital SARL', 'email' => 'hello@mktdigital.fr'],
            ['name' => 'Dev Solutions', 'email' => 'hello@devsolutions.fr'],
            ['name' => 'E-Commerce Pro', 'email' => 'support@ecommercepro.fr'],
        ];

        foreach ($customerNames as $data) {
            $customers[] = Customer::create([
                'organization_id' => $org->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => '01 23 45 67 89',
                'address' => '123 Rue de Paris, 75000 Paris',
                'tax_number' => 'FR' . rand(10000000000, 99999999999),
            ]);
        }

        // Create demo invoices
        $services = [
            ['description' => 'Consultation', 'unit_price' => 15000],
            ['description' => 'Développement Laravel', 'unit_price' => 12500],
            ['description' => 'Support technique', 'unit_price' => 10000],
            ['description' => 'Formation', 'unit_price' => 20000],
            ['description' => 'Auditory', 'unit_price' => 8000],
        ];

        for ($i = 0; $i < 8; $i++) {
            $service = $services[array_rand($services)];
            $customer = $customers[array_rand($customers)];
            $qty = rand(1, 5);

            $data = new InvoiceData(
                customer_id: $customer->id,
                issue_date: now()->subDays(rand(0, 30)),
                due_date: now()->addDays(rand(1, 60)),
                lines: [
                    new InvoiceLineData(
                        description: $service['description'],
                        quantity: $qty * 100,
                        unit_price: $service['unit_price'],
                        vat_rate: 20.00, // 20% VAT
                    ),
                ],
                notes: 'Facture ' . ($i + 1) . ' du mois',
                status: rand(0, 100) > 30 ? 'sent' : 'draft',
            );

            app(CreateInvoiceAction::class)->handle($data);
        }

        echo "✓ Seeding completed!\n";
        echo "  - Organization: {$org->name}\n";
        echo "  - User: {$user->email} (password: password)\n";
        echo "  - Customers: " . count($customers) . "\n";
        echo "  - Test invoices created\n";
    }
}

