<x-app-layout>
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Factures</h1>
            <p class="mt-2 text-sm text-gray-600">Gérez vos factures</p>
        </div>
        <a href="{{ route('invoices.create') }}" class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nouvelle facture
        </a>
    </div>

    {{-- Filters --}}
    <div class="space-y-4 rounded-lg bg-white p-4 shadow">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            {{-- Search --}}
            <flux:input
                wire:model.live="search"
                type="search"
                placeholder="Rechercher une facture..."
                icon="magnifying-glass"
            />

            {{-- Status Filter --}}
            <flux:select
                wire:model.live="status"
                label="Statut"
            >
                <option value="">Tous les statuts</option>
                <option value="draft">Brouillon</option>
                <option value="sent">Envoyée</option>
                <option value="paid">Payée</option>
                <option value="overdue">En retard</option>
            </flux:select>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-lg bg-white shadow">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Numéro</flux:table.column>
                <flux:table.column>Client</flux:table.column>
                <flux:table.column>Date</flux:table.column>
                <flux:table.column>Montant</flux:table.column>
                <flux:table.column>Statut</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($invoices as $invoice)
                    <flux:table.row>
                        <flux:table.cell>
                            <a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-blue-600 hover:text-blue-700">
                                {{ $invoice->number }}
                            </a>
                        </flux:table.cell>
                        <flux:table.cell>{{ $invoice->customer->name }}</flux:table.cell>
                        <flux:table.cell>{{ $invoice->issue_date->format('d/m/Y') }}</flux:table.cell>
                        <flux:table.cell>{{ number_format($invoice->total / 100, 2, ',', ' ') }}€</flux:table.cell>
                        <flux:table.cell>
                            @if ($invoice->status === 'draft')
                                <flux:badge color="gray">Brouillon</flux:badge>
                            @elseif ($invoice->status === 'sent')
                                <flux:badge color="blue">Envoyée</flux:badge>
                            @elseif ($invoice->status === 'paid')
                                <flux:badge color="green">Payée</flux:badge>
                            @elseif ($invoice->status === 'overdue')
                                <flux:badge color="red">En retard</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('invoices.edit', $invoice) }}" class="text-blue-600 hover:text-blue-700">
                                    Éditer
                                </a>
                                <button
                                    wire:click="deleteInvoice({{ $invoice->id }})"
                                    class="text-red-600 hover:text-red-700"
                                    onclick="confirm('Êtes-vous sûr ?') || event.preventDefault()"
                                >
                                    Supprimer
                                </button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center py-8 text-gray-500">
                            Aucune facture trouvée
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Pagination --}}
    <div class="flex justify-center">
        {{ $invoices->links() }}
    </div>
</div>
</x-app-layout>
