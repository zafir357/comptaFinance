<x-layouts.app>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">{{ $invoice->number }}</h1>
                <p class="mt-2 text-sm text-slate-400">
                    Client :
                    <a href="{{ route('customers.show', $invoice->customer) }}" class="text-blue-400 hover:underline">
                        {{ $invoice->customer->name }}
                    </a>
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                {{-- Download PDF button (for all invoices) --}}
                <a href="{{ route('invoices.download-pdf', $invoice) }}" target="_blank">
                    <flux:button icon="document-arrow-down" variant="primary">Télécharger PDF</flux:button>
                </a>

                {{-- Mark as paid — only for sent invoices --}}
                @if ($invoice->status === 'sent')
                    <form method="POST" action="{{ route('invoices.mark-paid', $invoice) }}" class="inline">
                        @csrf
                        <flux:button type="submit" variant="primary">Marquer comme payée</flux:button>
                    </form>
                @endif

                {{-- Edit — only for draft invoices --}}
                @if ($invoice->status === 'draft')
                    <a href="{{ route('invoices.edit', $invoice) }}">
                        <flux:button variant="filled">Éditer</flux:button>
                    </a>
                @endif

                {{-- Delete — only for draft invoices --}}
                @if ($invoice->status === 'draft')
                    <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" class="inline"
                          onsubmit="return confirm('Supprimer définitivement cette facture ?')">
                        @csrf
                        @method('DELETE')
                        <flux:button type="submit" variant="danger">Supprimer</flux:button>
                    </form>
                @endif

                <a href="{{ route('invoices.index') }}">
                    <flux:button variant="ghost">← Retour</flux:button>
                </a>
            </div>
        </div>

        {{-- Overdue warning --}}
        @if ($invoice->isOverdue())
            <div class="rounded-lg bg-red-900/30 border border-red-700 p-4 text-red-200 flex items-center gap-2 shadow-lg">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>
                    <strong>Facture en retard</strong> — l'échéance du {{ $invoice->due_date->format('d/m/Y') }} est dépassée.
                </span>
            </div>
        @endif

        {{-- Summary card --}}
        <flux:card class="shadow-lg">
            <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                <div>
                    <p class="text-sm text-slate-500">Date d'émission</p>
                    <p class="mt-1 font-medium">{{ $invoice->issue_date->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Date d'échéance</p>
                    <p class="mt-1 font-medium {{ $invoice->isOverdue() ? 'text-red-400' : '' }}">
                        {{ $invoice->due_date->format('d/m/Y') }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Statut</p>
                    <p class="mt-1">
                        @if ($invoice->isOverdue())
                            <flux:badge variant="danger">En retard</flux:badge>
                        @elseif ($invoice->status === 'draft')
                            <flux:badge>Brouillon</flux:badge>
                        @elseif ($invoice->status === 'sent')
                            <flux:badge variant="warning">Envoyée</flux:badge>
                        @elseif ($invoice->status === 'paid')
                            <flux:badge variant="success">Payée</flux:badge>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Total TTC</p>
                    <p class="mt-1 text-lg font-bold text-blue-400">{{ number_format($invoice->total / 100, 2, ',', ' ') }} €</p>
                </div>
            </div>
        </flux:card>

        {{-- Line Items --}}
        <flux:card class="shadow-lg">
            <h2 class="mb-4 text-lg font-semibold">Lignes de facture</h2>
            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Description</flux:table.column>
                        <flux:table.column>Qté</flux:table.column>
                        <flux:table.column>Prix unit. HT</flux:table.column>
                        <flux:table.column>TVA</flux:table.column>
                        <flux:table.column>Total HT</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($invoice->lines as $line)
                            <flux:table.row>
                                <flux:table.cell>{{ $line->description }}</flux:table.cell>
                                <flux:table.cell>{{ number_format($line->quantity / 100, 2, ',', ' ') }}</flux:table.cell>
                                <flux:table.cell>{{ number_format($line->unit_price / 100, 2, ',', ' ') }} €</flux:table.cell>
                                {{-- vat_rate stored in basis points: 2000 = 20%, 550 = 5.5% --}}
                                <flux:table.cell>{{ number_format($line->vat_rate / 100, 1) }} %</flux:table.cell>
                                <flux:table.cell class="font-medium">{{ number_format($line->total / 100, 2, ',', ' ') }} €</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        </flux:card>

        {{-- Totals --}}
        <flux:card class="shadow-lg">
            <div class="ml-auto max-w-xs space-y-2">
                <div class="flex justify-between text-slate-300">
                    <span>Sous-total HT</span>
                    <span>{{ number_format($invoice->subtotal / 100, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between border-t border-slate-700 pt-2 text-slate-300">
                    <span>TVA</span>
                    <span>{{ number_format($invoice->vat_total / 100, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between border-t border-slate-700 pt-2 text-xl font-bold">
                    <span>Total TTC</span>
                    <span class="text-blue-400">{{ number_format($invoice->total / 100, 2, ',', ' ') }} €</span>
                </div>
            </div>
        </flux:card>
    </div>
</x-layouts.app>
