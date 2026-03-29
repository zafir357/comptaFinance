{{-- VUE BLADE: Dashboard --}}
<div class="p-6">
    <div class="mb-6">
        <flux:heading size="xl">Tableau de bord</flux:heading>
        <flux:subheading class="mt-1">Vue d'ensemble de votre activité</flux:subheading>
    </div>

    @php $stats = $this->stats; @endphp

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <flux:card>
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-100 rounded-lg dark:bg-blue-900/30">
                    <flux:icon.document-text class="size-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <div class="text-sm text-gray-500">Facturé (total)</div>
                    <div class="text-2xl font-bold">{{ number_format($stats['total_invoiced'] / 100, 0, ',', ' ') }} €</div>
                    <div class="text-xs text-gray-400">{{ $stats['invoices_count'] }} facture(s)</div>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-4">
                <div class="p-3 bg-green-100 rounded-lg dark:bg-green-900/30">
                    <flux:icon.check-circle class="size-6 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <div class="text-sm text-gray-500">Encaissé</div>
                    <div class="text-2xl font-bold">{{ number_format($stats['total_paid'] / 100, 0, ',', ' ') }} €</div>
                    <div class="text-xs text-green-600">Payé</div>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-4">
                <div class="p-3 {{ $stats['overdue_count'] > 0 ? 'bg-red-100 dark:bg-red-900/30' : 'bg-yellow-100 dark:bg-yellow-900/30' }} rounded-lg">
                    <flux:icon.clock class="size-6 {{ $stats['overdue_count'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-yellow-600 dark:text-yellow-400' }}" />
                </div>
                <div>
                    <div class="text-sm text-gray-500">En attente</div>
                    <div class="text-2xl font-bold">{{ number_format($stats['total_pending'] / 100, 0, ',', ' ') }} €</div>
                    @if($stats['overdue_count'] > 0)
                        <div class="text-xs text-red-600">{{ $stats['overdue_count'] }} en retard</div>
                    @else
                        <div class="text-xs text-gray-400">À encaisser</div>
                    @endif
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-4">
                <div class="p-3 bg-orange-100 rounded-lg dark:bg-orange-900/30">
                    <flux:icon.receipt-percent class="size-6 text-orange-600 dark:text-orange-400" />
                </div>
                <div>
                    <div class="text-sm text-gray-500">Dépenses HT</div>
                    <div class="text-2xl font-bold">{{ number_format($stats['total_expenses'] / 100, 0, ',', ' ') }} €</div>
                    <div class="text-xs text-gray-400">{{ $stats['expenses_count'] }} dépense(s)</div>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Recent Activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Invoices --}}
        <flux:card>
            <div class="flex justify-between items-center mb-4">
                <flux:heading size="lg">Dernières factures</flux:heading>
                <flux:button href="{{ route('invoices.index') }}" variant="ghost" size="sm" wire:navigate>
                    Voir tout
                </flux:button>
            </div>

            @if($this->recentInvoices->count() > 0)
                <div class="space-y-3">
                    @foreach($this->recentInvoices as $invoice)
                        @php
                            $colors = ['draft' => 'zinc', 'sent' => 'blue', 'paid' => 'green', 'overdue' => 'red'];
                            $labels = ['draft' => 'Brouillon', 'sent' => 'Envoyée', 'paid' => 'Payée', 'overdue' => 'En retard'];
                        @endphp
                        <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-zinc-700 last:border-0">
                            <div>
                                <div class="font-medium text-sm">{{ $invoice->number }}</div>
                                <div class="text-xs text-gray-500">{{ $invoice->customer?->name }}</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <flux:badge color="{{ $colors[$invoice->status] ?? 'zinc' }}" size="sm">
                                    {{ $labels[$invoice->status] ?? $invoice->status }}
                                </flux:badge>
                                <div class="text-sm font-medium">{{ number_format($invoice->total / 100, 2, ',', ' ') }} €</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500 text-sm">
                    Aucune facture pour l'instant.
                    <a href="{{ route('invoices.create') }}" class="text-blue-600 hover:underline ml-1">Créer une facture</a>
                </div>
            @endif
        </flux:card>

        {{-- Recent Expenses --}}
        <flux:card>
            <div class="flex justify-between items-center mb-4">
                <flux:heading size="lg">Dernières dépenses</flux:heading>
                <flux:button href="{{ route('expenses.index') }}" variant="ghost" size="sm" wire:navigate>
                    Voir tout
                </flux:button>
            </div>

            @if($this->recentExpenses->count() > 0)
                <div class="space-y-3">
                    @foreach($this->recentExpenses as $expense)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-zinc-700 last:border-0">
                            <div>
                                <div class="font-medium text-sm">{{ $expense->supplier }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $expense->date->format('d/m/Y') }} · {{ $expense->category }}
                                </div>
                            </div>
                            <div class="text-sm font-medium">
                                {{ number_format($expense->amount / 100, 2, ',', ' ') }} € HT
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500 text-sm">
                    Aucune dépense pour l'instant.
                    <a href="{{ route('expenses.create') }}" class="text-blue-600 hover:underline ml-1">Enregistrer une dépense</a>
                </div>
            @endif
        </flux:card>
    </div>

    {{-- Quick Actions --}}
    <div class="mt-6">
        <flux:card>
            <flux:heading size="lg" class="mb-4">Actions rapides</flux:heading>
            <div class="flex flex-wrap gap-3">
                <flux:button href="{{ route('invoices.create') }}" variant="primary" wire:navigate>
                    <flux:icon.document-text class="size-5" />
                    Nouvelle facture
                </flux:button>
                <flux:button href="{{ route('expenses.create') }}" variant="outline" wire:navigate>
                    <flux:icon.receipt-percent class="size-5" />
                    Nouvelle dépense
                </flux:button>
                <flux:button href="{{ route('customers.create') }}" variant="outline" wire:navigate>
                    <flux:icon.user-plus class="size-5" />
                    Nouveau client
                </flux:button>
            </div>
        </flux:card>
    </div>
</div>
