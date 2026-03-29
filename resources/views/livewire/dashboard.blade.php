<div class="space-y-8">
    {{-- Page Header --}}
    <div>
        <h1 class="text-4xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-2 text-gray-600">
            Bienvenue dans {{ $currentOrg?->name ?? 'ComptaFinance' }}
        </p>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        {{-- Total Invoiced This Month --}}
        <flux:card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Facturé ce mois</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ number_format($stats['totalInvoicedThisMonth'] / 100, 0, ',', ' ') }}€
                    </p>
                </div>
                <svg class="h-12 w-12 text-blue-100" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
                </svg>
            </div>
        </flux:card>

        {{-- Outstanding Invoices --}}
        <flux:card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Factures impayées</p>
                    <p class="mt-2 text-3xl font-bold text-red-600">
                        {{ number_format($stats['outstandingInvoices'] / 100, 0, ',', ' ') }}€
                    </p>
                </div>
                <svg class="h-12 w-12 text-red-100" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
            </div>
        </flux:card>

        {{-- Total Expenses This Month --}}
        <flux:card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Dépenses ce mois</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ number_format($stats['totalExpensesThisMonth'] / 100, 0, ',', ' ') }}€
                    </p>
                </div>
                <svg class="h-12 w-12 text-green-100" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
                </svg>
            </div>
        </flux:card>

        {{-- Open Tickets --}}
        <flux:card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Tickets ouverts</p>
                    <p class="mt-2 text-3xl font-bold text-orange-600">
                        {{ $stats['openTickets'] }}
                    </p>
                </div>
                <svg class="h-12 w-12 text-orange-100" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
            </div>
        </flux:card>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Recent Invoices --}}
        <div class="lg:col-span-2">
            <flux:card>
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Factures récentes</h2>
                </div>

                <div class="overflow-x-auto">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Numéro</flux:table.column>
                            <flux:table.column>Client</flux:table.column>
                            <flux:table.column>Montant</flux:table.column>
                            <flux:table.column>Statut</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse ($stats['recentInvoices'] as $invoice)
                                <flux:table.row>
                                    <flux:table.cell>
                                        <a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-blue-600 hover:text-blue-700">
                                            {{ $invoice->number }}
                                        </a>
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $invoice->customer->name }}</flux:table.cell>
                                    <flux:table.cell>{{ number_format($invoice->total / 100, 2, ',', ' ') }}€</flux:table.cell>
                                    <flux:table.cell>
                                        @if ($invoice->status === 'draft')
                                            <flux:badge color="gray">Brouillon</flux:badge>
                                        @elseif ($invoice->status === 'sent')
                                            <flux:badge color="blue">Envoyée</flux:badge>
                                        @elseif ($invoice->status === 'paid')
                                            <flux:badge color="green">Payée</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="4" class="text-center py-4 text-gray-500">
                                        Aucune facture
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>

                <div class="mt-4">
                    <a href="{{ route('invoices.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        Voir toutes les factures →
                    </a>
                </div>
            </flux:card>
        </div>

        {{-- Top Customers --}}
        <div>
            <flux:card>
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Meilleurs clients</h2>
                </div>

                <div class="space-y-4">
                    @forelse ($stats['topCustomers'] as $customer)
                        <div class="flex items-center justify-between border-b pb-3">
                            <div>
                                <p class="font-medium text-gray-900">{{ $customer->name }}</p>
                                <p class="text-sm text-gray-600">{{ $customer->invoices_count }} factures</p>
                            </div>
                            <a href="{{ route('customers.show', $customer) }}" class="text-blue-600 hover:text-blue-700">
                                →
                            </a>
                        </div>
                    @empty
                        <p class="text-center py-8 text-gray-500 text-sm">Aucun client</p>
                    @endforelse
                </div>
            </flux:card>
        </div>
    </div>

    {{-- Quick Actions --}}
    <flux:card>
        <h2 class="mb-4 text-lg font-semibold text-gray-900">Actions rapides</h2>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <a href="{{ route('invoices.create') }}" class="flex items-center gap-2 rounded-lg bg-blue-50 px-4 py-3 text-blue-600 hover:bg-blue-100">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="text-sm font-medium">Nouvelle facture</span>
            </a>
            <a href="{{ route('customers.index') }}" class="flex items-center gap-2 rounded-lg bg-green-50 px-4 py-3 text-green-600 hover:bg-green-100">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="text-sm font-medium">Ajouter client</span>
            </a>
            <a href="{{ route('expenses.index') }}" class="flex items-center gap-2 rounded-lg bg-purple-50 px-4 py-3 text-purple-600 hover:bg-purple-100">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="text-sm font-medium">Note de frais</span>
            </a>
            <a href="{{ route('banking.index') }}" class="flex items-center gap-2 rounded-lg bg-yellow-50 px-4 py-3 text-yellow-600 hover:bg-yellow-100">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="text-sm font-medium">Rapprochement</span>
            </a>
        </div>
    </flux:card>
</div>
