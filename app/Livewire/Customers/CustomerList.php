<?php

namespace App\Livewire\Customers;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;
use App\Support\Tenancy\CurrentOrganization;

/**
 * COMPOSANT LIVEWIRE: CustomerList
 * 
 * Affiche la liste des clients de l'organization courante.
 * Avec recherche en temps réel et pagination.
 * 
 * LIVEWIRE TRAITS:
 * - WithPagination = ajoute la pagination (10, 20, 50 par page)
 * 
 * PROPRIÉTÉS PUBLIQUES = RÉACTIVES:
 * Quand tu changes $search dans la vue (via wire:model), 
 * Livewire refait automatiquement le render() → magie! ✨
 */
class CustomerList extends Component
{
    // TRAIT: Pagination Livewire
    use WithPagination;
    
    // PROPRIÉTÉ PUBLIQUE = accessible dans la vue + réactive
    // Quand l'user tape dans l'input de recherche, cette propriété se met à jour
    public string $search = '';
    
    /**
     * LIFECYCLE: updatedSearch()
     * 
     * S'exécute AUTOMATIQUEMENT quand $search change.
     * Livewire détecte "updated{PropertyName}" et l'appelle!
     * 
     * Pourquoi resetPage()? 
     * Si tu es sur la page 3 et tu cherches "Dupont", 
     * il faut retourner à la page 1 sinon tu peux avoir 0 résultats!
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }
    
    /**
     * MÉTHODE: getCustomersProperty()
     * 
     * Convention Livewire: get{Name}Property() → accessible comme $this->customers dans la vue
     * C'est un "computed property" (comme Vue.js ou Laravel accessors)
     * 
     * SÉCURITÉ MULTI-TENANT:
     * On filtre TOUJOURS par organization_id pour isoler les données!
     */
    public function getCustomersProperty()
    {
        return Customer::query()
            // SÉCURITÉ: Seulement les clients de l'org courante
            ->where('organization_id', CurrentOrganization::id())
            
            // RECHERCHE: Si $search n'est pas vide, filtrer par nom OU email
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            
            // TRI: Les plus récents en premier
            ->latest()
            
            // PAGINATION: 15 clients par page
            ->paginate(15);
    }
    
    /**
     * LIFECYCLE: render()
     * 
     * Retourne la vue Blade.
     * Le layout est défini globalement dans config/livewire.php.
     */
    public function render()
    {
        return view('livewire.customers.customer-list');
    }
}
