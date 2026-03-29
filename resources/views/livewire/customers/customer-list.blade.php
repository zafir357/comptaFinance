{{-- VUE BLADE: Liste des clients --}}

<div class="p-6">
    {{-- HEADER avec titre + bouton --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <flux:heading size="xl">Clients</flux:heading>
            <flux:subheading class="mt-1">Gérez vos clients</flux:subheading>
        </div>
        
        {{-- Bouton pour créer un nouveau client --}}
        <flux:button href="{{ route('customers.create') }}" variant="primary">
            <flux:icon.plus class="size-5" />
            Nouveau client
        </flux:button>
    </div>
    
    {{-- CARTE principale --}}
    <flux:card>
        {{-- RECHERCHE en temps réel --}}
        <div class="mb-6">
            <flux:input 
                wire:model.live="search" 
                type="search" 
                placeholder="Rechercher par nom ou email..." 
                icon="magnifying-glass"
            />
            {{-- 
                wire:model.live = binding en temps réel
                Chaque frappe → $search change → render() s'exécute → liste filtrée!
            --}}
        </div>
        
        {{-- TABLE des clients --}}
        @if($this->customers->count() > 0)
            <flux:table>
                <flux:columns>
                    <flux:column>Nom</flux:column>
                    <flux:column>Email</flux:column>
                    <flux:column>Téléphone</flux:column>
                    <flux:column>Ville</flux:column>
                    <flux:column>Créé le</flux:column>
                    <flux:column></flux:column>
                </flux:columns>
                
                <flux:rows>
                    @foreach($this->customers as $customer)
                        <flux:row :key="$customer->id">
                            <flux:cell>
                                <div class="font-medium">{{ $customer->name }}</div>
                                @if($customer->tax_number)
                                    <div class="text-xs text-gray-500">{{ $customer->tax_number }}</div>
                                @endif
                            </flux:cell>
                            
                            <flux:cell>{{ $customer->email ?? '—' }}</flux:cell>
                            
                            <flux:cell>{{ $customer->phone ?? '—' }}</flux:cell>
                            
                            <flux:cell>
                                @if($customer->address)
                                    {{ Str::limit($customer->address, 30) }}
                                @else
                                    —
                                @endif
                            </flux:cell>
                            
                            <flux:cell>
                                <div class="text-sm text-gray-600">
                                    {{ $customer->created_at->format('d/m/Y') }}
                                </div>
                            </flux:cell>
                            
                            <flux:cell>
                                <flux:button size="sm" variant="ghost">
                                    Voir
                                </flux:button>
                            </flux:cell>
                        </flux:row>
                    @endforeach
                </flux:rows>
            </flux:table>
            
            {{-- PAGINATION --}}
            <div class="mt-6">
                {{ $this->customers->links() }}
            </div>
        @else
            {{-- ÉTAT VIDE --}}
            <div class="text-center py-12">
                <flux:icon.users class="size-12 text-gray-400 mx-auto mb-4" />
                
                @if($search)
                    <flux:heading size="lg">Aucun client trouvé</flux:heading>
                    <flux:subheading class="mt-2">
                        Aucun résultat pour "{{ $search }}"
                    </flux:subheading>
                @else
                    <flux:heading size="lg">Aucun client</flux:heading>
                    <flux:subheading class="mt-2">
                        Commencez par créer votre premier client
                    </flux:subheading>
                    <flux:button href="{{ route('customers.create') }}" variant="primary" class="mt-4">
                        Créer un client
                    </flux:button>
                @endif
            </div>
        @endif
    </flux:card>
</div>
