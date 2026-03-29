<x-app-layout>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Notes de frais</h1>
                <p class="mt-2 text-sm text-gray-600">Gérez vos dépenses et justificatifs</p>
            </div>
            <a href="{{ route('expenses.create') }}" class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nouvelle dépense
            </a>
        </div>

        {{-- Filters --}}
        <div class="space-y-4 rounded-lg bg-white p-4 shadow">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <flux:input
                    wire:model.live="search"
                    type="search"
                    placeholder="Rechercher un fournisseur..."
                    icon="magnifying-glass"
                />
                <flux:select
                    wire:model.live="category"
                    label="Catégorie"
                >
                    <option value="">Toutes les catégories</option>
                    <option value="travel">Déplacements</option>
                    <option value="meals">Repas</option>
                    <option value="supplies">Fournitures</option>
                    <option value="utilities">Services</option>
                    <option value="other">Autre</option>
                </flux:select>
                <flux:select
                    wire:model.live="status"
                    label="Statut justificatif"
                >
                    <option value="">Tous les statuts</option>
                    <option value="pending">En attente</option>
                    <option value="processed">Traité</option>
                    <option value="failed">Erreur</option>
                </flux:select>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto rounded-lg bg-white shadow">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Fournisseur</flux:table.column>
                    <flux:table.column>Catégorie</flux:table.column>
                    <flux:table.column>Date</flux:table.column>
                    <flux:table.column>Montant</flux:table.column>
                    <flux:table.column>Justificatif</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($expenses as $expense)
                        <flux:table.row>
                            <flux:table.cell>{{ $expense->supplier }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge>{{ ucfirst($expense->category) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $expense->date->format('d/m/Y') }}</flux:table.cell>
                            <flux:table.cell class="font-medium">
                                {{ number_format(($expense->amount + $expense->vat_amount) / 100, 2, ',', ' ') }}€
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($expense->receipt_status === 'pending')
                                    <flux:badge color="yellow">En attente...</flux:badge>
                                @elseif ($expense->receipt_status === 'processed')
                                    <flux:badge color="green">✓ Traité</flux:badge>
                                @elseif ($expense->receipt_status === 'failed')
                                    <flux:badge color="red">✗ Erreur</flux:badge>
                                @else
                                    <span class="text-gray-500 text-sm">Aucun</span>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center py-8 text-gray-500">
                                Aucune dépense trouvée
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        {{-- Pagination --}}
        <div class="flex justify-center">
            {{ $expenses->links() }}
        </div>
    </div>
</x-app-layout>
