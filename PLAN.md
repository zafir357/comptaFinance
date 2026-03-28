# 🚀 INSTRUCTIONS DE DÉMARRAGE — ComptaFinance

## ✅ ÉTAPE 1 : Vérification de l'environnement (30 minutes)

### 1.1 Vérifier Laravel
```bash
php artisan --version
# Doit être Laravel 11.x
```

### 1.2 Vérifier la base de données
```bash
php artisan migrate:status
# Si erreur de connexion, configurer .env
```

### 1.3 Installer Flux UI (CRITIQUE!)
```bash
# Aller sur fluxui.dev et suivre les instructions
# C'est le composant le plus important pour ce job!

# Installation typique :
composer require livewire/flux-pro

# Publier les assets
php artisan flux:install
```

### 1.4 Installer les autres packages
```bash
composer require barryvdh/laravel-dompdf
composer require league/csv
composer require pestphp/pest --dev
composer require pestphp/pest-plugin-laravel --dev
composer require laravel/pint --dev
```

---

## ✅ ÉTAPE 2 : Créer les migrations (1 heure)

### 2.1 Créer TOUTES les migrations en une fois
```bash
# Multi-tenancy
php artisan make:migration create_organizations_table
php artisan make:migration create_memberships_table

# Clients
php artisan make:migration create_customers_table

# Facturation
php artisan make:migration create_invoices_table
php artisan make:migration create_invoice_lines_table

# Dépenses
php artisan make:migration create_expenses_table

# Banque
php artisan make:migration create_bank_transactions_table
php artisan make:migration create_reconciliations_table

# Support
php artisan make:migration create_tickets_table
php artisan make:migration create_ticket_messages_table

# Queue (pour les jobs)
php artisan queue:table
```

### 2.2 Remplir les migrations

**organizations:**
```php
Schema::create('organizations', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->json('settings')->nullable();
    $table->timestamps();
});
```

**memberships:**
```php
Schema::create('memberships', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
    $table->enum('role', ['owner', 'accountant', 'viewer'])->default('viewer');
    $table->timestamps();
    
    $table->unique(['user_id', 'organization_id']);
});
```

**customers:**
```php
Schema::create('customers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('email')->nullable();
    $table->string('phone')->nullable();
    $table->text('address')->nullable();
    $table->string('tax_number')->nullable();
    $table->timestamps();
    
    $table->index(['organization_id']);
});
```

**invoices:**
```php
Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
    $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
    $table->string('number'); // 2026-0001
    $table->enum('status', ['draft', 'sent', 'paid', 'overdue'])->default('draft');
    $table->date('issue_date');
    $table->date('due_date');
    $table->integer('subtotal')->default(0); // en centimes
    $table->integer('vat_total')->default(0);
    $table->integer('total')->default(0);
    $table->text('notes')->nullable();
    $table->timestamp('sent_at')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->timestamps();
    
    $table->unique(['organization_id', 'number']);
    $table->index(['organization_id', 'status']);
});
```

**invoice_lines:**
```php
Schema::create('invoice_lines', function (Blueprint $table) {
    $table->id();
    $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
    $table->string('description');
    $table->decimal('quantity', 10, 2)->default(1);
    $table->integer('unit_price'); // en centimes
    $table->decimal('vat_rate', 5, 2)->default(20.00); // 20%
    $table->integer('total'); // en centimes
    $table->timestamps();
});
```

**expenses:**
```php
Schema::create('expenses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
    $table->string('category'); // carburant, fournitures, etc.
    $table->string('supplier');
    $table->date('date');
    $table->integer('amount'); // HT en centimes
    $table->integer('vat_amount')->default(0); // TVA en centimes
    $table->string('receipt_path')->nullable();
    $table->enum('receipt_status', ['uploaded', 'processing', 'processed', 'failed'])->default('uploaded');
    $table->timestamp('receipt_processed_at')->nullable();
    $table->timestamps();
    
    $table->index(['organization_id', 'category']);
});
```

**bank_transactions:**
```php
Schema::create('bank_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
    $table->date('date');
    $table->string('description');
    $table->integer('amount'); // en centimes
    $table->string('currency', 3)->default('EUR');
    $table->string('external_id'); // pour idempotence
    $table->boolean('reconciled')->default(false);
    $table->timestamps();
    
    $table->unique(['organization_id', 'external_id']);
    $table->index(['organization_id', 'reconciled']);
});
```

**reconciliations:**
```php
Schema::create('reconciliations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('bank_transaction_id')->constrained()->cascadeOnDelete();
    $table->morphs('reconcilable'); // Invoice ou Expense
    $table->timestamps();
});
```

**tickets:**
```php
Schema::create('tickets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('subject');
    $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');
    $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
    $table->json('tags')->nullable();
    $table->timestamps();
    
    $table->index(['organization_id', 'status']);
});
```

**ticket_messages:**
```php
Schema::create('ticket_messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->text('body');
    $table->boolean('is_internal')->default(false);
    $table->timestamps();
});
```

### 2.3 Exécuter les migrations
```bash
php artisan migrate
```

---

## ✅ ÉTAPE 3 : Créer les modèles (30 minutes)

```bash
php artisan make:model Organization
php artisan make:model Membership
php artisan make:model Customer
php artisan make:model Invoice
php artisan make:model InvoiceLine
php artisan make:model Expense
php artisan make:model BankTransaction
php artisan make:model Reconciliation
php artisan make:model Ticket
php artisan make:model TicketMessage
```

### Exemple: Invoice Model
```php
// app/Models/Invoice.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Invoice extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
    ];
    
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
    
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }
    
    public function reconciliations(): MorphMany
    {
        return $this->morphMany(Reconciliation::class, 'reconcilable');
    }
    
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
    
    // Helpers
    public function getTotalInEurosAttribute()
    {
        return $this->total / 100;
    }
}
```

---

## ✅ ÉTAPE 4 : Infrastructure Multi-tenant (1 heure)

### 4.1 Créer CurrentOrganization helper
```bash
mkdir -p app/Support/Tenancy
```

Créer `app/Support/Tenancy/CurrentOrganization.php`:
```php
<?php

namespace App\Support\Tenancy;

use App\Models\Organization;

class CurrentOrganization
{
    protected static ?Organization $organization = null;
    
    public static function set(?Organization $organization): void
    {
        static::$organization = $organization;
    }
    
    public static function get(): ?Organization
    {
        return static::$organization;
    }
    
    public static function id(): ?int
    {
        return static::$organization?->id;
    }
    
    public static function check(): bool
    {
        return static::$organization !== null;
    }
}
```

### 4.2 Créer SetCurrentOrganization middleware
```bash
php artisan make:middleware SetCurrentOrganization
```

Éditer `app/Http/Middleware/SetCurrentOrganization.php`:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Support\Tenancy\CurrentOrganization;

class SetCurrentOrganization
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()) {
            return $next($request);
        }
        
        // Récupérer l'org depuis la session ou la première disponible
        $orgId = session('current_organization_id');
        
        if (! $orgId) {
            $membership = $request->user()->memberships()->first();
            if ($membership) {
                $orgId = $membership->organization_id;
                session(['current_organization_id' => $orgId]);
            }
        }
        
        if ($orgId) {
            $organization = $request->user()
                ->organizations()
                ->find($orgId);
                
            if ($organization) {
                CurrentOrganization::set($organization);
            }
        }
        
        return $next($request);
    }
}
```

### 4.3 Enregistrer le middleware
Éditer `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\SetCurrentOrganization::class,
    ]);
})
```

### 4.4 Mettre à jour User model
Ajouter les relations dans `app/Models/User.php`:
```php
public function memberships()
{
    return $this->hasMany(Membership::class);
}

public function organizations()
{
    return $this->belongsToMany(Organization::class, 'memberships')
        ->withPivot('role')
        ->withTimestamps();
}

public function currentOrganization()
{
    return Organization::find(session('current_organization_id'));
}
```

---

## ✅ ÉTAPE 5 : Premier test Flux UI (30 minutes)

### 5.1 Créer une page de test
```bash
php artisan make:livewire TestFlux
```

Créer `resources/views/livewire/test-flux.blade.php`:
```blade
<?php

use function Livewire\Volt\{state};

state(['name' => '']);

?>

<div class="p-6">
    <flux:heading size="xl">Test Flux UI</flux:heading>
    
    <flux:card class="mt-4">
        <flux:input wire:model="name" label="Votre nom" placeholder="Entrez votre nom" />
        
        <flux:button class="mt-4" variant="primary">
            Envoyer
        </flux:button>
        
        @if($name)
            <flux:badge class="mt-4" color="green">Bonjour {{ $name }}!</flux:badge>
        @endif
    </flux:card>
</div>
```

### 5.2 Ajouter une route
Dans `routes/web.php`:
```php
Route::get('/test-flux', function () {
    return view('livewire.test-flux');
})->middleware('auth');
```

### 5.3 Tester
```bash
# Démarrer le serveur
php artisan serve

# Ouvrir http://localhost:8000/test-flux
# Vérifier que Flux UI fonctionne bien
```

**✅ Si Flux UI fonctionne → continuez**
**❌ Si Flux UI ne marche pas → réparer avant de continuer!**

---

## ✅ ÉTAPE 6 : Module Facturation - Backend (2 heures)

### 6.1 Créer la structure
```bash
mkdir -p app/Domain/Billing/Invoices/{Actions,Data,Services,Repositories}
mkdir -p app/Http/Requests/Invoices
mkdir -p app/Policies
```

### 6.2 InvoiceData (DTO)
Créer `app/Domain/Billing/Invoices/Data/InvoiceData.php`:
```php
<?php

namespace App\Domain\Billing\Invoices\Data;

class InvoiceData
{
    public function __construct(
        public int $customerId,
        public string $issueDate,
        public string $dueDate,
        public array $lines,
        public ?string $notes = null,
    ) {}
    
    public static function fromRequest(array $data): self
    {
        return new self(
            customerId: $data['customer_id'],
            issueDate: $data['issue_date'],
            dueDate: $data['due_date'],
            lines: $data['lines'] ?? [],
            notes: $data['notes'] ?? null,
        );
    }
}
```

### 6.3 InvoiceNumberingService
Créer `app/Domain/Billing/Invoices/Services/InvoiceNumberingService.php`:
```php
<?php

namespace App\Domain\Billing\Invoices\Services;

use App\Models\Invoice;

class InvoiceNumberingService
{
    public function generate(int $organizationId): string
    {
        $year = date('Y');
        
        $lastInvoice = Invoice::where('organization_id', $organizationId)
            ->where('number', 'like', $year . '-%')
            ->orderBy('number', 'desc')
            ->first();
        
        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->number, 5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $year . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
```

### 6.4 CreateInvoiceAction
Créer `app/Domain/Billing/Invoices/Actions/CreateInvoiceAction.php`:
```php
<?php

namespace App\Domain\Billing\Invoices\Actions;

use App\Domain\Billing\Invoices\Data\InvoiceData;
use App\Domain\Billing\Invoices\Services\InvoiceNumberingService;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Support\Tenancy\CurrentOrganization;

class CreateInvoiceAction
{
    public function __construct(
        private InvoiceNumberingService $numberingService
    ) {}
    
    public function execute(InvoiceData $data): Invoice
    {
        $organizationId = CurrentOrganization::id();
        
        $invoice = Invoice::create([
            'organization_id' => $organizationId,
            'customer_id' => $data->customerId,
            'number' => $this->numberingService->generate($organizationId),
            'issue_date' => $data->issueDate,
            'due_date' => $data->dueDate,
            'notes' => $data->notes,
            'status' => 'draft',
        ]);
        
        $subtotal = 0;
        $vatTotal = 0;
        
        foreach ($data->lines as $line) {
            $lineTotal = $line['quantity'] * $line['unit_price'];
            $lineVat = $lineTotal * ($line['vat_rate'] / 100);
            
            InvoiceLine::create([
                'invoice_id' => $invoice->id,
                'description' => $line['description'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'vat_rate' => $line['vat_rate'],
                'total' => $lineTotal,
            ]);
            
            $subtotal += $lineTotal;
            $vatTotal += $lineVat;
        }
        
        $invoice->update([
            'subtotal' => $subtotal,
            'vat_total' => $vatTotal,
            'total' => $subtotal + $vatTotal,
        ]);
        
        return $invoice->fresh();
    }
}
```

### 6.5 InvoicePolicy
```bash
php artisan make:policy InvoicePolicy --model=Invoice
```

Éditer `app/Policies/InvoicePolicy.php`:
```php
public function viewAny(User $user): bool
{
    return true;
}

public function view(User $user, Invoice $invoice): bool
{
    return $user->organizations->contains($invoice->organization_id);
}

public function create(User $user): bool
{
    return CurrentOrganization::check();
}

public function update(User $user, Invoice $invoice): bool
{
    if (!$user->organizations->contains($invoice->organization_id)) {
        return false;
    }
    
    $membership = $user->memberships()
        ->where('organization_id', $invoice->organization_id)
        ->first();
    
    return in_array($membership->role, ['owner', 'accountant']);
}
```

---

## ✅ PROCHAINES ÉTAPES

Vous avez maintenant la **fondation complète** :
- ✅ Migrations créées
- ✅ Modèles avec relations
- ✅ Multi-tenancy fonctionnel
- ✅ Flux UI testé
- ✅ Module facturation (backend)

**Continuez avec :**
1. Module facturation (frontend Livewire + Flux UI)
2. Module dépenses
3. Module banque
4. Dashboard
5. Tests
6. Déploiement

Le plan complet est dans `PLAN.md` - suivez Day 1, Day 2, Day 3.

**Bon courage! 🚀**
