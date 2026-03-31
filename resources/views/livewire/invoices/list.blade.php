<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-white">Factures</h1>
            <p class="mt-2 text-sm text-slate-400">Gérez vos factures clients</p>
        </div>
        <a href="{{ route('invoices.create') }}" class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 transition">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nouvelle facture
        </a>
    </div>

    {{-- Filters --}}
    <div class="space-y-4 rounded-lg bg-slate-900 p-4 border border-slate-700 shadow-lg">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            {{-- Search --}}
            <flux:input
                wire:model.live="search"
                type="search"
                placeholder="Rechercher par numéro ou client..."
                icon="magnifying-glass"
                class="bg-slate-800 border-slate-600 text-white placeholder-slate-500"
            />

            {{-- Status Filter --}}
            <flux:select
                wire:model.live="status"
                label="Statut"
                class="bg-slate-800 border-slate-600 text-white"
            >
                <option value="">Tous les statuts</option>
                <option value="draft">Brouillon</option>
                <option value="sent">Envoyée</option>
                <option value="paid">Payée</option>
            </flux:select>
        </div>
    </div>

    {{-- Results count --}}
    <p class="text-sm text-slate-400">{{ $invoices->total() }} facture(s)</p>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-lg bg-slate-900 border border-slate-700 shadow-lg">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Numéro</flux:table.column>
                <flux:table.column>Client</flux:table.column>
                <flux:table.column>Date émission</flux:table.column>
                <flux:table.column>Échéance</flux:table.column>
                <flux:table.column>Montant TTC</flux:table.column>
                <flux:table.column>Statut</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($invoices as $invoice)
                    <flux:table.row class="border-b border-slate-700 hover:bg-slate-800/50">
                        <flux:table.cell>
                            <a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-blue-400 hover:text-blue-300">
                                {{ $invoice->number }}
                            </a>
                        </flux:table.cell>
                        <flux:table.cell>
                            {{-- Link to customer page --}}
                            <a href="{{ route('customers.show', $invoice->customer) }}" class="text-slate-300 hover:text-blue-400 hover:underline">
                                {{ $invoice->customer->name }}
                            </a>
                        </flux:table.cell>
                        <flux:table.cell class="text-slate-400">{{ $invoice->issue_date->format('d/m/Y') }}</flux:table.cell>
                        <flux:table.cell class="text-slate-400">{{ $invoice->due_date->format('d/m/Y') }}</flux:table.cell>
                        <flux:table.cell class="font-medium text-white">{{ number_format($invoice->total / 100, 2, ',', ' ') }} €</flux:table.cell>
                        <flux:table.cell>
                            {{-- Overdue takes priority over "sent" --}}
                            @if ($invoice->isOverdue())
                                <flux:badge color="danger" class="bg-red-900 text-red-100">En retard</flux:badge>
                            @elseif ($invoice->status === 'draft')
                                <flux:badge color="gray" class="bg-slate-700 text-slate-100">Brouillon</flux:badge>
                            @elseif ($invoice->status === 'sent')
                                <flux:badge color="warning" class="bg-amber-900 text-amber-100">Envoyée</flux:badge>
                            @elseif ($invoice->status === 'paid')
                                <flux:badge color="success" class="bg-emerald-900 text-emerald-100">Payée</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                @if ($invoice->status === 'draft')
                                    <a href="{{ route('invoices.edit', $invoice) }}" class="text-blue-400 hover:text-blue-300 text-sm">
                                        Éditer
                                    </a>
                                @endif
                                <a href="{{ route('invoices.show', $invoice) }}" class="text-slate-400 hover:text-slate-300 text-sm">
                                    Voir
                                </a>
                                <a href="{{ route('invoices.download-pdf', $invoice) }}" target="_blank" class="text-slate-400 hover:text-slate-300 text-sm" title="Télécharger PDF">
                                    PDF
                                </a>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7">
                            <div class="py-12 text-center">
                                <p class="text-slate-500 mb-4">Aucune facture trouvée</p>
                                <a href="{{ route('invoices.create') }}">
                                    <flux:button variant="primary" size="sm">Créer une facture</flux:button>
                                </a>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Pagination --}}
    @if ($invoices->hasPages())
        <div class="flex justify-center">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
