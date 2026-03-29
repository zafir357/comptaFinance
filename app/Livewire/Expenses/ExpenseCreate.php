<?php

namespace App\Livewire\Expenses;

use App\Models\Expense;
use App\Support\Tenancy\CurrentOrganization;
use Livewire\Component;

class ExpenseCreate extends Component
{
    public string $category = '';

    public string $supplier = '';

    public string $date = '';

    public string $amount = '';

    public string $vat_amount = '0';

    public string $notes = '';

    public array $commonCategories = [
        'Carburant',
        'Fournitures de bureau',
        'Repas & Restauration',
        'Transport',
        'Hébergement',
        'Abonnements & Logiciels',
        'Équipement informatique',
        'Téléphone',
        'Publicité & Marketing',
        'Formation',
        'Autre',
    ];

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'category' => 'required|string|max:100',
            'supplier' => 'required|string|max:255',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'vat_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ];
    }

    protected $messages = [
        'category.required' => 'La catégorie est obligatoire.',
        'supplier.required' => 'Le fournisseur est obligatoire.',
        'date.required' => 'La date est obligatoire.',
        'amount.required' => 'Le montant HT est obligatoire.',
        'amount.numeric' => 'Le montant doit être un nombre.',
        'amount.min' => 'Le montant doit être positif.',
    ];

    public function save(): void
    {
        $validated = $this->validate();

        Expense::create([
            'organization_id' => CurrentOrganization::id(),
            'category' => $validated['category'],
            'supplier' => $validated['supplier'],
            'date' => $validated['date'],
            'amount' => (int) round((float) $validated['amount'] * 100), // convert to centimes
            'vat_amount' => (int) round((float) ($validated['vat_amount'] ?? 0) * 100),
            'receipt_status' => 'uploaded',
        ]);

        session()->flash('success', 'Dépense enregistrée avec succès!');
        $this->redirect(route('expenses.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.expenses.expense-create');
    }
}
