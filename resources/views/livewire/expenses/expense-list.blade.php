{{-- VUE BLADE: Liste des dépenses --}}
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <flux:heading size="xl">Dépenses</flux:heading>
            <flux:subheading class="mt-1">Gérez vos notes de frais et dépenses</flux:subheading>
        </div>
        <flux:button href="{{ route('expenses.create') }}" variant="primary" wire:navigate>
            <flux:icon.plus class="size-5" />
            Nouvelle dépense
        </flux:button>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <flux:card>
        <div class="flex gap-4 mb-6">
            <div class="flex-1">
                <flux:input
                    wire:model.live="search"
                    type="search"
                    placeholder="Rechercher par fournisseur ou catégorie..."
                    icon="magnifying-glass"
                />
            </div>
            @if(count($this->categories) > 0)
                <flux:select wire:model.live="categoryFilter" placeholder="Toutes les catégories">
                    <flux:select.option value="">Toutes les catégories</flux:select.option>
                    @foreach($this->categories as $cat)
                        <flux:select.option value="{{ $cat }}">{{ $cat }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif
        </div>

        @if($this->expenses->count() > 0)
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Date</flux:table.column>
                    <flux:table.column>Fournisseur</flux:table.column>
                    <flux:table.column>Catégorie</flux:table.column>
                    <flux:table.column>Montant HT</flux:table.column>
                    <flux:table.column>TVA</flux:table.column>
                    <flux:table.column>Total TTC</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($this->expenses as $expense)
                        <flux:table.row wire:key="{{ $expense->id }}">
                            <flux:table.cell>
                                {{ $expense->date->format('d/m/Y') }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="font-medium">{{ $expense->supplier }}</div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge color="zinc" size="sm">{{ $expense->category }}</flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ number_format($expense->amount / 100, 2, ',', ' ') }} €
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ number_format($expense->vat_amount / 100, 2, ',', ' ') }} €
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="font-medium">
                                    {{ number_format(($expense->amount + $expense->vat_amount) / 100, 2, ',', ' ') }} €
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div class="mt-6">
                {{ $this->expenses->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <flux:icon.receipt-percent class="size-12 text-gray-400 mx-auto mb-4" />

                @if($search || $categoryFilter)
                    <flux:heading size="lg">Aucune dépense trouvée</flux:heading>
                    <flux:subheading class="mt-2">Modifiez vos critères de recherche</flux:subheading>
                @else
                    <flux:heading size="lg">Aucune dépense</flux:heading>
                    <flux:subheading class="mt-2">Enregistrez votre première dépense</flux:subheading>
                    <flux:button href="{{ route('expenses.create') }}" variant="primary" class="mt-4" wire:navigate>
                        Nouvelle dépense
                    </flux:button>
                @endif
            </div>
        @endif
    </flux:card>
</div>
