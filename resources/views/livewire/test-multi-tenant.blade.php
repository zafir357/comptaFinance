{{-- VUE BLADE pour le composant TestMultiTenant --}}

<div class="p-6">
    <flux:heading size="xl">🧪 Test Multi-Tenant</flux:heading>
    
    <flux:card class="mt-4">
        <flux:heading size="lg">CurrentOrganization Helper</flux:heading>
        
        <div class="mt-4">
            <flux:badge color="green" size="lg">Infrastructure Multi-Tenant OK</flux:badge>
        </div>
        
        {{-- Affichage des infos de l'organization --}}
        <div class="mt-6 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg font-mono text-sm whitespace-pre-line">
{{ $organizationInfo }}
        </div>
        
        <div class="mt-6">
            <flux:heading size="md">Explications</flux:heading>
            <ul class="mt-2 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                <li>✅ Le middleware <code>SetCurrentOrganization</code> s'est exécuté</li>
                <li>✅ Il a chargé l'organization depuis la session</li>
                <li>✅ <code>CurrentOrganization::get()</code> retourne l'organization</li>
                <li>✅ Tu peux maintenant filtrer toutes tes requêtes par <code>organization_id</code></li>
            </ul>
        </div>
        
        <div class="mt-6">
            <flux:heading size="md">Prochaine étape</flux:heading>
            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                On va créer le module <strong>Facturation</strong> avec Livewire + Flux UI!
            </p>
        </div>
    </flux:card>
</div>
