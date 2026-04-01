<?php

use App\Domain\Banking\Actions\ImportBankTransactionsAction;
use App\Domain\Banking\Services\BankTransactionCsvParser;
use App\Domain\Billing\Invoices\Data\CustomerData;
use App\Domain\Expenses\Actions\CreateExpenseAction;
use App\Domain\Expenses\Data\ExpenseData;
use App\Models\Customer;
use App\Models\Organization;
use App\Support\Tenancy\CurrentOrganization;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/*
|--------------------------------------------------------------------------
| IMPORT COMMANDS - Following Domain Architecture (DTO → Action → Repository)
|--------------------------------------------------------------------------
*/

/**
 * Import bank transactions from CSV
 * 
 * Architecture Flow:
 * CSV → BankTransactionCsvParser (Service) → BankTransactionData (DTO) 
 * → ImportBankTransactionsAction → BankTransactionRepository
 * 
 * Usage: 
 *   php artisan import:bank
 *   php artisan import:bank csv/bank_transactions.csv
 *   php artisan import:bank csv/bank_transactions.csv --org=1
 */
Artisan::command('import:bank {file?} {--org=}', function (?string $file = null) {
    $filePath = $file ?? base_path('csv/bank_transactions.csv');
    
    if (!file_exists($filePath)) {
        $this->error("❌ File not found: {$filePath}");
        return 1;
    }

    // Set organization context
    $orgId = $this->option('org');
    $organization = $orgId ? Organization::findOrFail($orgId) : Organization::first();
    
    if (!$organization) {
        $this->error("❌ No organization found. Create one first.");
        return 1;
    }
    
    app(CurrentOrganization::class)->set($organization);
    $this->info("📂 Organization: {$organization->name}");
    $this->info("📄 File: {$filePath}");

    try {
        // Service: Parse CSV → DTO[]
        $content = file_get_contents($filePath);
        $parser = app(BankTransactionCsvParser::class);
        $transactionDTOs = $parser->parse($content);
        
        $this->info("📊 Parsed: " . count($transactionDTOs) . " transactions");

        // Action: DTO[] → Repository → Models
        $action = app(ImportBankTransactionsAction::class);
        $imported = $action->handle($transactionDTOs);

        $this->newLine();
        $this->info("✅ IMPORTED: " . $imported->count() . " new transactions");
        $this->info("⏭️  SKIPPED: " . (count($transactionDTOs) - $imported->count()) . " duplicates");

        return 0;
    } catch (\Exception $e) {
        $this->error("❌ Error: " . $e->getMessage());
        return 1;
    }
})->purpose('Import bank transactions from CSV (Domain: Banking)');


/**
 * Import/Seed customers from CSV
 * 
 * Architecture Flow:
 * CSV → CustomerData (DTO) → Customer Model (via Repository pattern)
 * 
 * Usage:
 *   php artisan import:customers
 *   php artisan import:customers csv/customers_import.csv
 */
Artisan::command('import:customers {file?} {--org=}', function (?string $file = null) {
    $filePath = $file ?? base_path('csv/customers_import.csv');
    
    if (!file_exists($filePath)) {
        $this->error("❌ File not found: {$filePath}");
        return 1;
    }

    // Set organization context
    $orgId = $this->option('org');
    $organization = $orgId ? Organization::findOrFail($orgId) : Organization::first();
    
    if (!$organization) {
        $this->error("❌ No organization found.");
        return 1;
    }
    
    app(CurrentOrganization::class)->set($organization);
    $this->info("📂 Organization: {$organization->name}");
    $this->info("📄 File: {$filePath}");

    try {
        $content = file_get_contents($filePath);
        $lines = array_filter(explode("\n", trim($content)));
        
        // Skip header
        $header = str_getcsv(array_shift($lines));
        
        $created = 0;
        $skipped = 0;

        foreach ($lines as $line) {
            $row = str_getcsv($line);
            if (count($row) < 2) continue;
            
            $data = array_combine($header, $row);
            
            // Check if exists (idempotent)
            if (Customer::where('email', $data['email'])->exists()) {
                $skipped++;
                continue;
            }

            // Create via DTO pattern
            $customerData = CustomerData::fromArray([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'address' => trim(($data['address'] ?? '') . ', ' . ($data['city'] ?? '') . ' ' . ($data['postal_code'] ?? '')),
                'tax_number' => $data['vat_number'] ?? $data['siret'] ?? null,
            ]);

            Customer::create([
                'organization_id' => $organization->id,
                'name' => $customerData->name,
                'email' => $customerData->email,
                'phone' => $customerData->phone,
                'address' => $customerData->address,
                'tax_number' => $customerData->tax_number,
            ]);
            
            $created++;
        }

        $this->newLine();
        $this->info("✅ CREATED: {$created} customers");
        $this->info("⏭️  SKIPPED: {$skipped} existing");

        return 0;
    } catch (\Exception $e) {
        $this->error("❌ Error: " . $e->getMessage());
        return 1;
    }
})->purpose('Import customers from CSV (Domain: Billing)');


/**
 * Import expenses from CSV
 * 
 * Architecture Flow:
 * CSV → ExpenseData (DTO) → CreateExpenseAction → ExpenseRepository
 * 
 * Usage:
 *   php artisan import:expenses
 *   php artisan import:expenses csv/expenses_import.csv
 */
Artisan::command('import:expenses {file?} {--org=}', function (?string $file = null) {
    $filePath = $file ?? base_path('csv/expenses_import.csv');
    
    if (!file_exists($filePath)) {
        $this->error("❌ File not found: {$filePath}");
        return 1;
    }

    // Set organization context
    $orgId = $this->option('org');
    $organization = $orgId ? Organization::findOrFail($orgId) : Organization::first();
    
    if (!$organization) {
        $this->error("❌ No organization found.");
        return 1;
    }
    
    app(CurrentOrganization::class)->set($organization);
    $this->info("📂 Organization: {$organization->name}");
    $this->info("📄 File: {$filePath}");

    try {
        $content = file_get_contents($filePath);
        $lines = array_filter(explode("\n", trim($content)));
        
        // Skip header
        $header = str_getcsv(array_shift($lines));
        
        $created = 0;

        foreach ($lines as $line) {
            $row = str_getcsv($line);
            if (count($row) < 5) continue;
            
            $data = array_combine($header, $row);

            // Create via DTO → Action pattern
            $expenseData = ExpenseData::fromArray([
                'category' => $data['category'],
                'supplier' => $data['supplier'],
                'amount' => (float) str_replace(',', '.', $data['amount']),
                'vat_amount' => (float) str_replace(',', '.', $data['vat_amount']),
                'date' => $data['date'],
                'notes' => $data['description'] ?? null,
            ]);

            app(CreateExpenseAction::class)->handle($expenseData);
            $created++;
        }

        $this->newLine();
        $this->info("✅ CREATED: {$created} expenses");

        return 0;
    } catch (\Exception $e) {
        $this->error("❌ Error: " . $e->getMessage());
        return 1;
    }
})->purpose('Import expenses from CSV (Domain: Expenses)');


/**
 * Import ALL test data at once
 * 
 * Usage:
 *   php artisan import:all
 */
Artisan::command('import:all {--org=}', function () {
    $this->info("🚀 IMPORTING ALL TEST DATA");
    $this->newLine();

    $orgOption = $this->option('org') ? "--org={$this->option('org')}" : '';

    // 1. Customers first (invoices depend on them)
    $this->call('import:customers', array_filter(['--org' => $this->option('org')]));
    $this->newLine();

    // 2. Bank transactions
    $this->call('import:bank', array_filter(['--org' => $this->option('org')]));
    $this->newLine();

    // 3. Expenses
    $this->call('import:expenses', array_filter(['--org' => $this->option('org')]));
    $this->newLine();

    $this->info("🎉 ALL IMPORTS COMPLETE!");
    
    return 0;
})->purpose('Import all test data (customers, bank transactions, expenses)');
