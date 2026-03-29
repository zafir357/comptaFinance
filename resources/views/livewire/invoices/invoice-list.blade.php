{{-- VUE BLADE: Liste des factures --}}
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <flux:heading size="xl">Factures</flux:heading>
            <flux:subheading class="mt-1">Gérez vos factures</flux:subheading>
        </div>
        <flux:button href="{{ route('invoices.create') }}" variant="primary" wire:navigate>
            <flux:icon.plus class="size-5" />
            Nouvelle facture
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
                    placeholder="Rechercher par numéro ou client..."
                    icon="magnifying-glass"
                />
            </div>
            <flux:select wire:model.live="statusFilter" placeholder="Tous les statuts">
                <flux:select.option value="">Tous les statuts</flux:select.option>
                <flux:select.option value="draft">Brouillon</flux:select.option>
                <flux:select.option value="sent">Envoyée</flux:select.option>
                <flux:select.option value="paid">Payée</flux:select.option>
                <flux:select.option value="overdue">En retard</flux:select.option>
            </flux:select>
        </div>

        @if($this->invoices->count() > 0)
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Numéro</flux:table.column>
                    <flux:table.column>Client</flux:table.column>
                    <flux:table.column>Date</flux:table.column>
                    <flux:table.column>Échéance</flux:table.column>
                    <flux:table.column>Montant TTC</flux:table.column>
                    <flux:table.column>Statut</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($this->invoices as $invoice)
                        <flux:table.row wire:key="{{ $invoice->id }}">
                            <flux:table.cell>
                                <div class="font-medium">{{ $invoice->number }}</div>
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $invoice->customer?->name ?? '—' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $invoice->issue_date->format('d/m/Y') }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <span class="{{ $invoice->isOverdue() ? 'text-red-600 font-medium' : '' }}">
                                    {{ $invoice->due_date->format('d/m/Y') }}
                                </span>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="font-medium">{{ number_format($invoice->total / 100, 2, ',', ' ') }} €</div>
                            </flux:table.cell>

                            <flux:table.cell>
                                @php
                                    $colors = [
                                        'draft' => 'zinc',
                                        'sent' => 'blue',
                                        'paid' => 'green',
                                        'overdue' => 'red',
                                    ];
                                    $labels = [
                                        'draft' => 'Brouillon',
                                        'sent' => 'Envoyée',
                                        'paid' => 'Payée',
                                        'overdue' => 'En retard',
                                    ];
                                @endphp
                                <flux:badge color="{{ $colors[$invoice->status] ?? 'zinc' }}" size="sm">
                                    {{ $labels[$invoice->status] ?? $invoice->status }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:button size="sm" variant="ghost" href="{{ route('invoices.show', $invoice) }}" wire:navigate>
                                    Voir
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div class="mt-6">
                {{ $this->invoices->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <flux:icon.document-text class="size-12 text-gray-400 mx-auto mb-4" />

                @if($search || $statusFilter)
                    <flux:heading size="lg">Aucune facture trouvée</flux:heading>
                    <flux:subheading class="mt-2">Modifiez vos critères de recherche</flux:subheading>
                @else
                    <flux:heading size="lg">Aucune facture</flux:heading>
                    <flux:subheading class="mt-2">Créez votre première facture</flux:subheading>
                    <flux:button href="{{ route('invoices.create') }}" variant="primary" class="mt-4" wire:navigate>
                        Créer une facture
                    </flux:button>
                @endif
            </div>
        @endif
    </flux:card>
</div>
