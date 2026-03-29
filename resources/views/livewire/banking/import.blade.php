<x-app-layout>
    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Importer transactions bancaires</h1>
            <p class="mt-2 text-gray-600">Importer un CSV depuis votre banque</p>
        </div>

        <flux:card>
            <div class="space-y-6">
                {{-- File Upload --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Fichier CSV
                    </label>
                    <flux:input
                        wire:model="csvFile"
                        type="file"
                        accept=".csv,.txt"
                    />
                    <p class="mt-2 text-xs text-gray-500">
                        Format: date,description,amount,external_id
                    </p>
                </div>

                {{-- Preview Button --}}
                @if ($csvFile && !$showPreview)
                    <flux:button wire:click="preview" variant="secondary">
                        Aperçu
                    </flux:button>
                @endif

                {{-- Preview Modal --}}
                @if ($showPreview && !empty($preview))
                    <div class="rounded-lg border-2 border-blue-200 bg-blue-50 p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">Aperçu (premiers enregistrements)</h3>
                        <div class="overflow-x-auto">
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>Date</flux:table.column>
                                    <flux:table.column>Description</flux:table.column>
                                    <flux:table.column>Montant</flux:table.column>
                                    <flux:table.column>ID Externe</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows>
                                    @foreach ($preview as $transaction)
                                        <flux:table.row>
                                            <flux:table.cell>{{ $transaction->date?->format('d/m/Y') }}</flux:table.cell>
                                            <flux:table.cell>{{ $transaction->description }}</flux:table.cell>
                                            <flux:table.cell>
                                                <span class="{{ $transaction->amount >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                                    {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount / 100, 2, ',', ' ') }}€
                                                </span>
                                            </flux:table.cell>
                                            <flux:table.cell class="text-xs text-gray-600">{{ $transaction->external_id }}</flux:table.cell>
                                        </flux:table.row>
                                    @endforeach
                                </flux:table.rows>
                            </flux:table>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <flux:button
                                wire:click="import"
                                wire:loading.attr="disabled"
                                variant="primary"
                            >
                                @if ($importing)
                                    Importation en cours...
                                @else
                                    Importer
                                @endif
                            </flux:button>
                            <flux:button
                                wire:click="$set('showPreview', false)"
                                variant="secondary"
                            >
                                Annuler
                            </flux:button>
                        </div>
                    </div>
                @endif
            </div>
        </flux:card>

        {{-- Help Section --}}
        <flux:card>
            <h3 class="font-semibold text-gray-900 mb-3">Format du fichier CSV</h3>
            <div class="bg-gray-100 p-3 rounded text-sm font-mono text-gray-700 overflow-x-auto">
                date,description,amount,external_id<br>
                2026-03-25,Virement Client Acme,5000.00,TRN123456<br>
                2026-03-24,Prélèvement SARL EDF,-120.50,TRN123457
            </div>
            <p class="mt-3 text-sm text-gray-600">
                • Les montants peuvent avoir décimales ou virgules<br>
                • Les montants positifs = crédits, négatifs = débits<br>
                • L'external_id doit être unique (utilisé pour idempotence)
            </p>
        </flux:card>
    </div>
</x-app-layout>
