<?php

namespace Tests\Feature\Invoices;

use App\Domain\Billing\Invoices\Actions\CreateInvoiceAction;
use App\Domain\Billing\Invoices\Data\InvoiceData;
use App\Models\Customer;
use App\Models\Organization;
use App\Models\User;
use App\Support\Tenancy\CurrentOrganization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateInvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createApplication();
    }

    public function test_can_create_invoice_with_lines_and_correct_totals()
    {
        // Create org and user
        $org = Organization::create(['name' => 'Test Org']);
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password',
        ]);
        $org->members()->attach($user, ['role' => 'owner']);

        // Set organization context
        app(CurrentOrganization::class)->set($org);

        // Create customer
        $customer = Customer::create([
            'organization_id' => $org->id,
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
        ]);

        // Create invoice data
        $data = new InvoiceData(
            customer_id: $customer->id,
            issue_date: now(),
            due_date: now()->addDays(30),
            lines: [
                new \App\Domain\Billing\Invoices\Data\InvoiceLineData(
                    description: 'Service 1',
                    quantity: 100,  // 1.00
                    unit_price: 10000, // 100.00
                    vat_rate: 2000  // 20%
                ),
                new \App\Domain\Billing\Invoices\Data\InvoiceLineData(
                    description: 'Service 2',
                    quantity: 200,  // 2.00
                    unit_price: 5000, // 50.00
                    vat_rate: 2000  // 20%
                ),
            ],
            notes: 'Test invoice',
        );

        // Create invoice
        $action = app(CreateInvoiceAction::class);
        $invoice = $action->handle($data);

        // Assertions
        $this->assertNotNull($invoice->id);
        $this->assertEquals('2026-0001', $invoice->number);
        $this->assertEquals($customer->id, $invoice->customer_id);
        $this->assertEquals($org->id, $invoice->organization_id);

        // Totals: (1*100 + 2*50) = 200 HT, 40 TVA, 240 TTC
        $this->assertEquals(20000, $invoice->subtotal); // 200€
        $this->assertEquals(4000, $invoice->vat_total);  // 40€
        $this->assertEquals(24000, $invoice->total);     // 240€

        $this->assertCount(2, $invoice->lines);
    }

    public function test_invoice_scoped_to_organization()
    {
        $org1 = Organization::create(['name' => 'Org 1']);
        $org2 = Organization::create(['name' => 'Org 2']);

        // Create customer in org1
        $customer = Customer::create([
            'organization_id' => $org1->id,
            'name' => 'Customer 1',
            'email' => 'c1@test.com',
        ]);

        // Set org1 context
        app(CurrentOrganization::class)->set($org1);

        // Create invoice in org1
        $data = new InvoiceData(
            customer_id: $customer->id,
            issue_date: now(),
            due_date: now()->addDays(30),
            lines: [],
        );

        $invoice = app(CreateInvoiceAction::class)->handle($data);

        // Verify invoice belongs to org1
        $this->assertEquals($org1->id, $invoice->organization_id);

        // Now switch to org2 and query
        app(CurrentOrganization::class)->set($org2);

        // Invoice should not be visible in org2 context
        $this->assertNull(\App\Models\Invoice::find($invoice->id));
    }
}
