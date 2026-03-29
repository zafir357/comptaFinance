<div class="space-y-8">
    {{-- Page Header --}}
    <div class="overflow-hidden rounded-3xl border border-slate-700 bg-gradient-to-r from-slate-900 via-slate-850 to-slate-800 p-6 text-white shadow-2xl shadow-slate-900/50 lg:p-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-blue-300">Tableau de bord</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">Bienvenue dans {{ $currentOrg?->name ?? 'ComptaFinance' }}</h1>
                <p class="mt-3 max-w-xl text-sm leading-6 text-slate-400 sm:text-base">
                    Suivez votre facturation, vos dépenses et vos tickets avec une vue claire, rapide et pensée pour la productivité.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:flex">
                <a href="{{ route('invoices.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-lg shadow-black/50 transition hover:-translate-y-0.5 hover:shadow-xl">
                    Nouvelle facture
                </a>
                <a href="{{ route('banking.import') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-600 bg-slate-800 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">
                    Import bancaire
                </a>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        {{-- Total Invoiced This Month — links to all invoices --}}
        <a href="{{ route('invoices.index') }}" class="block">
            <flux:card class="overflow-hidden border border-slate-700 bg-slate-900 shadow-lg transition hover:-translate-y-0.5 hover:shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-400">Facturé ce mois</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-white">
                            {{ number_format($stats['totalInvoicedThisMonth'] / 100, 0, ',', ' ') }} €
                        </p>
                    </div>
                    <div class="rounded-2xl bg-blue-900/50 p-3 text-blue-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </flux:card>
        </a>

        {{-- Outstanding Invoices — links to sent (unpaid) invoices --}}
        <a href="{{ route('invoices.index', ['status' => 'sent']) }}" class="block">
            <flux:card class="overflow-hidden border border-slate-700 bg-slate-900 shadow-lg transition hover:-translate-y-0.5 hover:shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-400">Factures impayées</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-rose-400">
                            {{ number_format($stats['outstandingInvoices'] / 100, 0, ',', ' ') }} €
                        </p>
                    </div>
                    <div class="rounded-2xl bg-rose-900/50 p-3 text-rose-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </flux:card>
        </a>

        {{-- Total Expenses This Month — links to expenses --}}
        <a href="{{ route('expenses.index') }}" class="block">
            <flux:card class="overflow-hidden border border-slate-700 bg-slate-900 shadow-lg transition hover:-translate-y-0.5 hover:shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-400">Dépenses ce mois</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-white">
                            {{ number_format($stats['totalExpensesThisMonth'] / 100, 0, ',', ' ') }} €
                        </p>
                    </div>
                    <div class="rounded-2xl bg-emerald-900/50 p-3 text-emerald-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </flux:card>
        </a>

        {{-- Open Tickets — links to tickets --}}
        <a href="{{ route('tickets.index') }}" class="block">
            <flux:card class="overflow-hidden border border-slate-700 bg-slate-900 shadow-lg transition hover:-translate-y-0.5 hover:shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-400">Tickets ouverts</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-amber-400">
                            {{ $stats['openTickets'] }}
                        </p>
                    </div>
                    <div class="rounded-2xl bg-amber-900/50 p-3 text-amber-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
            </flux:card>
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Recent Invoices --}}
        <div class="lg:col-span-2">
            <flux:card class="border border-slate-700 bg-slate-900 shadow-lg">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold tracking-tight text-white">Factures récentes</h2>
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
                                <flux:table.row class="border-b border-slate-700 hover:bg-slate-800/50">
                                    <flux:table.cell>
                                        <a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-blue-400 hover:text-blue-300">
                                            {{ $invoice->number }}
                                        </a>
                                    </flux:table.cell>
                                    <flux:table.cell class="text-slate-300">{{ $invoice->customer->name }}</flux:table.cell>
                                    <flux:table.cell class="text-slate-300">{{ number_format($invoice->total / 100, 2, ',', ' ') }}€</flux:table.cell>
                                    <flux:table.cell>
                                        @if ($invoice->status === 'draft')
                                            <flux:badge color="gray" class="bg-slate-700 text-slate-100">Brouillon</flux:badge>
                                        @elseif ($invoice->status === 'sent')
                                            <flux:badge color="blue" class="bg-blue-700 text-blue-100">Envoyée</flux:badge>
                                        @elseif ($invoice->status === 'paid')
                                            <flux:badge color="green" class="bg-emerald-700 text-emerald-100">Payée</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="4" class="py-4 text-center text-slate-500">
                                        Aucune facture
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>

                <div class="mt-4">
                    <a href="{{ route('invoices.index') }}" class="text-sm font-semibold text-blue-400 hover:text-blue-300">
                        Voir toutes les factures →
                    </a>
                </div>
            </flux:card>
        </div>

        {{-- Top Customers --}}
        <div>
            <flux:card class="border border-slate-700 bg-slate-900 shadow-lg">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold tracking-tight text-white">Meilleurs clients</h2>
                </div>

                <div class="space-y-4">
                    @forelse ($stats['topCustomers'] as $customer)
                        <div class="flex items-center justify-between border-b border-slate-700 pb-3">
                            <div>
                                <p class="font-medium text-white">{{ $customer->name }}</p>
                                <p class="text-sm text-slate-400">{{ $customer->invoices_count }} factures</p>
                            </div>
                            <a href="{{ route('customers.show', $customer) }}" class="text-blue-400 hover:text-blue-300">
                                →
                            </a>
                        </div>
                    @empty
                        <p class="py-8 text-center text-sm text-slate-500">Aucun client</p>
                    @endforelse
                </div>
            </flux:card>
        </div>
    </div>

    {{-- Quick Actions --}}
    <flux:card class="border border-slate-700 bg-slate-900 shadow-lg">
        <h2 class="mb-4 text-lg font-semibold tracking-tight text-white">Actions rapides</h2>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <a href="{{ route('invoices.create') }}" class="flex items-center gap-3 rounded-2xl bg-blue-900/60 px-4 py-3 text-blue-200 transition hover:bg-blue-900/80">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-sm font-medium">Nouvelle facture</span>
            </a>
            <a href="{{ route('customers.index') }}" class="flex items-center gap-3 rounded-2xl bg-emerald-900/60 px-4 py-3 text-emerald-200 transition hover:bg-emerald-900/80">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="text-sm font-medium">Ajouter client</span>
            </a>
            <a href="{{ route('expenses.create') }}" class="flex items-center gap-3 rounded-2xl bg-violet-900/60 px-4 py-3 text-violet-200 transition hover:bg-violet-900/80">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span class="text-sm font-medium">Note de frais</span>
            </a>
            <a href="{{ route('banking.import') }}" class="flex items-center gap-3 rounded-2xl bg-amber-900/60 px-4 py-3 text-amber-200 transition hover:bg-amber-900/80">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <span class="text-sm font-medium">Import bancaire</span>
            </a>
        </div>
    </flux:card>
</div>
