<x-app-layout>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $invoice->number }}</h1>
                <p class="mt-2 text-sm text-gray-600">{{ $invoice->customer->name }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('invoices.edit', $invoice) }}" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Éditer
                </a>
                <a href="{{ route('invoices.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50">
                    Retour
                </a>
            </div>
        </div>

        {{-- Content --}}
        <flux:card>
            <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                <div>
                    <p class="text-sm text-gray-600">Date d'émission</p>
                    <p class="mt-1 font-medium">{{ $invoice->issue_date->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Date d'échéance</p>
                    <p class="mt-1 font-medium">{{ $invoice->due_date->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Statut</p>
                    <p class="mt-1">
                        @if ($invoice->status === 'draft')
                            <flux:badge color="gray">Brouillon</flux:badge>
                        @elseif ($invoice->status === 'sent')
                            <flux:badge color="blue">Envoyée</flux:badge>
                        @elseif ($invoice->status === 'paid')
                            <flux:badge color="green">Payée</flux:badge>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total</p>
                    <p class="mt-1 text-lg font-bold text-blue-600">{{ number_format($invoice->total / 100, 2, ',', ' ') }}€</p>
                </div>
            </div>
        </flux:card>

        {{-- Line Items --}}
        <flux:card>
            <h2 class="mb-4 text-lg font-semibold">Lignes de facture</h2>
            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Description</flux:table.column>
                        <flux:table.column>Qty</flux:table.column>
                        <flux:table.column>Prix unit.</flux:table.column>
                        <flux:table.column>TVA</flux:table.column>
                        <flux:table.column>Total</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($invoice->lines as $line)
                            <flux:table.row>
                                <flux:table.cell>{{ $line->description }}</flux:table.cell>
                                <flux:table.cell>{{ $line->quantity }}</flux:table.cell>
                                <flux:table.cell>{{ number_format($line->unit_price / 100, 2, ',', ' ') }}€</flux:table.cell>
                                <flux:table.cell>{{ $line->vat_rate }}%</flux:table.cell>
                                <flux:table.cell>{{ number_format($line->total / 100, 2, ',', ' ') }}€</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        </flux:card>

        {{-- Totals --}}
        <flux:card>
            <div class="max-w-sm space-y-2 ml-auto">
                <div class="flex justify-between text-gray-700">
                    <span>Sous-total</span>
                    <span>{{ number_format($invoice->subtotal / 100, 2, ',', ' ') }}€</span>
                </div>
                <div class="flex justify-between border-t pt-2 text-gray-700">
                    <span>TVA</span>
                    <span>{{ number_format($invoice->vat_total / 100, 2, ',', ' ') }}€</span>
                </div>
                <div class="flex justify-between border-t border-gray-300 pt-2 text-lg font-bold">
                    <span>Total TTC</span>
                    <span>{{ number_format($invoice->total / 100, 2, ',', ' ') }}€</span>
                </div>
            </div>
        </flux:card>
    </div>
</x-app-layout>
