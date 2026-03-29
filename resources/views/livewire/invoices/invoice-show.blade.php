{{-- VUE BLADE: Détail d'une facture --}}
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <flux:heading size="xl">Facture {{ $invoice->number }}</flux:heading>
            <flux:subheading class="mt-1">
                @php
                    $labels = ['draft' => 'Brouillon', 'sent' => 'Envoyée', 'paid' => 'Payée', 'overdue' => 'En retard'];
                    $colors = ['draft' => 'zinc', 'sent' => 'blue', 'paid' => 'green', 'overdue' => 'red'];
                @endphp
                <flux:badge color="{{ $colors[$invoice->status] ?? 'zinc' }}">
                    {{ $labels[$invoice->status] ?? $invoice->status }}
                </flux:badge>
            </flux:subheading>
        </div>
        <div class="flex gap-2">
            @if($invoice->status === 'draft')
                <flux:button wire:click="markAsSent" variant="primary">
                    <flux:icon.paper-airplane class="size-5" />
                    Marquer comme envoyée
                </flux:button>
            @elseif($invoice->status === 'sent')
                <flux:button wire:click="markAsPaid" variant="primary">
                    <flux:icon.check-circle class="size-5" />
                    Marquer comme payée
                </flux:button>
            @endif
            <flux:button href="{{ route('invoices.index') }}" variant="ghost" wire:navigate>
                Retour
            </flux:button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Client info --}}
            <flux:card>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <div class="text-sm text-gray-500 mb-1">Client</div>
                        <div class="font-semibold">{{ $invoice->customer?->name ?? '—' }}</div>
                        @if($invoice->customer?->email)
                            <div class="text-sm text-gray-600">{{ $invoice->customer->email }}</div>
                        @endif
                        @if($invoice->customer?->address)
                            <div class="text-sm text-gray-600 mt-1 whitespace-pre-line">{{ $invoice->customer->address }}</div>
                        @endif
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 mb-1">Dates</div>
                        <div class="text-sm">
                            <span class="text-gray-600">Émission:</span>
                            {{ $invoice->issue_date->format('d/m/Y') }}
                        </div>
                        <div class="text-sm mt-1">
                            <span class="text-gray-600">Échéance:</span>
                            <span class="{{ $invoice->isOverdue() ? 'text-red-600 font-medium' : '' }}">
                                {{ $invoice->due_date->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </flux:card>

            {{-- Lines --}}
            <flux:card>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Description</flux:table.column>
                        <flux:table.column>Qté</flux:table.column>
                        <flux:table.column>Prix HT</flux:table.column>
                        <flux:table.column>TVA</flux:table.column>
                        <flux:table.column>Total HT</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($invoice->lines as $line)
                            <flux:table.row wire:key="{{ $line->id }}">
                                <flux:table.cell>{{ $line->description }}</flux:table.cell>
                                <flux:table.cell>{{ $line->quantity }}</flux:table.cell>
                                <flux:table.cell>{{ number_format($line->unit_price / 100, 2, ',', ' ') }} €</flux:table.cell>
                                <flux:table.cell>{{ $line->vat_rate }} %</flux:table.cell>
                                <flux:table.cell>{{ number_format($line->total / 100, 2, ',', ' ') }} €</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>

                @if($invoice->notes)
                    <div class="mt-4 text-sm text-gray-600">
                        <span class="font-medium">Notes:</span> {{ $invoice->notes }}
                    </div>
                @endif
            </flux:card>
        </div>

        <div>
            <flux:card>
                <flux:heading size="lg" class="mb-4">Récapitulatif</flux:heading>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Sous-total HT</span>
                        <span>{{ number_format($invoice->subtotal / 100, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">TVA</span>
                        <span>{{ number_format($invoice->vat_total / 100, 2, ',', ' ') }} €</span>
                    </div>
                    <flux:separator />
                    <div class="flex justify-between font-bold text-lg">
                        <span>Total TTC</span>
                        <span>{{ number_format($invoice->total / 100, 2, ',', ' ') }} €</span>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</div>
