<?php

namespace App\Livewire;

use Livewire\Component;
use App\Support\Tenancy\CurrentOrganization;

/**
 * COMPOSANT LIVEWIRE: TestMultiTenant
 * 
 * Composant de test pour vérifier que l'infrastructure multi-tenant fonctionne.
 * 
 * COMMENT ÇA MARCHE:
 * 1. L'user fait une requête vers /test-multi-tenant
 * 2. Le middleware SetCurrentOrganization charge l'org dans CurrentOrganization
 * 3. Le composant Livewire se monte (mount() s'exécute)
 * 4. On récupère l'org via CurrentOrganization::get()
 * 5. On affiche les infos dans la vue Blade
 */
class TestMultiTenant extends Component
{
    // Propriété publique = accessible dans la vue Blade
    public string $organizationInfo = '';
    
    /**
     * LIFECYCLE: mount()
     * 
     * S'exécute UNE SEULE FOIS quand le composant est créé.
     * Équivalent du __construct() mais avec accès à la session, user, etc.
     */
    public function mount()
    {
        // Récupérer l'organization courante depuis le helper
        $org = CurrentOrganization::get();
        
        if ($org) {
            // Organization trouvée ✅
            $this->organizationInfo = "✅ Multi-tenant fonctionne!\n\n";
            $this->organizationInfo .= "Organization ID: {$org->id}\n";
            $this->organizationInfo .= "Nom: {$org->name}\n";
            $this->organizationInfo .= "Slug: {$org->slug}\n\n";
            $this->organizationInfo .= "User: " . auth()->user()->name . "\n";
            $this->organizationInfo .= "Email: " . auth()->user()->email . "\n";
            $this->organizationInfo .= "Rôle: " . auth()->user()->roleInOrganization($org->id);
        } else {
            // Pas d'organization ❌
            $this->organizationInfo = "❌ Aucune organization active!";
        }
    }
    
    /**
     * LIFECYCLE: render()
     * 
     * S'exécute à CHAQUE requête (y compris les requêtes AJAX de Livewire).
     * Retourne la vue Blade à afficher.
     * 
     * Le layout est défini globalement dans config/livewire.php,
     * donc plus besoin de spécifier ->layout() ici!
     */
    public function render()
    {
        return view('livewire.test-multi-tenant');
    }
}
