<?php

namespace App\Livewire\Customers;

use Livewire\Component;
use App\Models\Customer;
use App\Support\Tenancy\CurrentOrganization;

/**
 * COMPOSANT LIVEWIRE: CustomerCreate
 * 
 * Formulaire pour créer un nouveau client.
 * 
 * VALIDATION LIVEWIRE:
 * - Définie dans $rules
 * - S'exécute automatiquement dans save()
 * - Messages d'erreur affichés automatiquement dans la vue
 * 
 * FORM DATA BINDING:
 * Toutes les propriétés publiques sont liées aux inputs via wire:model
 */
class CustomerCreate extends Component
{
    // PROPRIÉTÉS DU FORMULAIRE = liées aux inputs
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $tax_number = '';
    
    /**
     * RÈGLES DE VALIDATION
     * 
     * Laravel validation rules appliquées automatiquement.
     * Livewire affiche les erreurs à côté de chaque input!
     */
    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'tax_number' => 'nullable|string|max:50',
        ];
    }
    
    /**
     * MESSAGES DE VALIDATION personnalisés (en français)
     */
    protected $messages = [
        'name.required' => 'Le nom est obligatoire.',
        'email.email' => 'L\'adresse email n\'est pas valide.',
    ];
    
    /**
     * MÉTHODE: save()
     * 
     * Appelée quand l'user clique sur "Enregistrer".
     * wire:submit="save" dans le formulaire.
     * 
     * FLOW:
     * 1. $this->validate() → valide selon $rules
     * 2. Si erreurs → stop + affiche erreurs
     * 3. Si OK → crée le customer
     * 4. Flash message de succès
     * 5. Redirige vers la liste
     */
    public function save()
    {
        // VALIDATION
        $validated = $this->validate();
        
        // CRÉATION du client
        Customer::create([
            'organization_id' => CurrentOrganization::id(),  // MULTI-TENANT: toujours lier à l'org!
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'tax_number' => $validated['tax_number'],
        ]);
        
        // MESSAGE DE SUCCÈS (flash session)
        session()->flash('success', 'Client créé avec succès!');
        
        // REDIRECTION vers la liste
        return redirect()->route('customers.index');
    }
    
    /**
     * LIFECYCLE: render()
     */
    public function render()
    {
        return view('livewire.customers.customer-create');
    }
}
