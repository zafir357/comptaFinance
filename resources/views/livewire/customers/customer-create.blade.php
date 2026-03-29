{{-- VUE BLADE: Créer un client --}}

<div class="p-6">
    {{-- HEADER --}}
    <div class="mb-6">
        <flux:heading size="xl">Nouveau client</flux:heading>
        <flux:subheading class="mt-1">Ajoutez un nouveau client à votre portefeuille</flux:subheading>
    </div>
    
    {{-- FORMULAIRE --}}
    <flux:card>
        <form wire:submit="save">
            {{-- 
                wire:submit="save" 
                Quand le form est soumis → appelle la méthode save() du composant
                Livewire empêche le submit normal et fait un appel AJAX
            --}}
            
            <div class="space-y-6">
                {{-- NOM (obligatoire) --}}
                <flux:input 
                    wire:model="name" 
                    label="Nom *" 
                    placeholder="Ex: SARL TechnoWeb"
                    required
                />
                {{-- 
                    wire:model="name" 
                    Lie l'input à la propriété $name du composant
                    Quand tu tapes → $name se met à jour automatiquement
                --}}
                
                {{-- EMAIL --}}
                <flux:input 
                    wire:model="email" 
                    type="email"
                    label="Email" 
                    placeholder="contact@client.fr"
                />
                
                {{-- TÉLÉPHONE --}}
                <flux:input 
                    wire:model="phone" 
                    label="Téléphone" 
                    placeholder="01 23 45 67 89"
                />
                
                {{-- NUMÉRO TVA / SIRET --}}
                <flux:input 
                    wire:model="tax_number" 
                    label="SIRET / N° TVA" 
                    placeholder="123 456 789 00012"
                />
                
                {{-- ADRESSE --}}
                <flux:textarea 
                    wire:model="address" 
                    label="Adresse complète" 
                    placeholder="12 rue de la République&#10;75001 Paris"
                    rows="3"
                />
            </div>
            
            {{-- BOUTONS --}}
            <div class="flex justify-end gap-3 mt-8">
                <flux:button 
                    href="{{ route('customers.index') }}" 
                    variant="ghost"
                >
                    Annuler
                </flux:button>
                
                <flux:button 
                    type="submit" 
                    variant="primary"
                >
                    <flux:icon.check class="size-5" />
                    Enregistrer
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>
