<x-app-layout>
    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-white">Importer transactions bancaires</h1>
            <p class="mt-2 text-slate-400">Importer un CSV depuis votre banque</p>
        </div>

        <flux:card class="bg-slate-900 border border-slate-700">
            <div class="space-y-6">
                {{-- File Upload --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Fichier CSV
                    </label>
                    <flux:input
                        wire:model="csvFile"
                        type="file"
                        accept=".csv,.txt"
                        class="bg-slate-800 border-slate-600 text-white"
                    />
                    <p class="mt-2 text-xs text-slate-500">
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
                    <div class="rounded-lg border-2 border-blue-700 bg-blue-900/30 p-4 backdrop-blur">
                        <h3 class="font-semibold text-white mb-3">Aperçu (premiers enregistrements)</h3>
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
                                        <flux:table.row class="border-b border-slate-700">
                                            <flux:table.cell class="text-slate-300">{{ $transaction->date?->format('d/m/Y') }}</flux:table.cell>
                                            <flux:table.cell class="text-slate-300">{{ $transaction->description }}</flux:table.cell>
                                            <flux:table.cell>
                                                <span class="{{ $transaction->amount >= 0 ? 'text-emerald-400' : 'text-red-400' }} font-medium">
                                                    {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount / 100, 2, ',', ' ') }}€
                                                </span>
                                            </flux:table.cell>
                                            <flux:table.cell class="text-xs text-slate-500">{{ $transaction->external_id }}</flux:table.cell>
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
        <flux:card class="bg-slate-900 border border-slate-700">
            <h3 class="font-semibold text-white mb-3">Format du fichier CSV</h3>
            <div class="bg-slate-800 p-3 rounded text-sm font-mono text-slate-300 overflow-x-auto border border-slate-600">
                date,description,amount,external_id<br>
                2026-03-25,Virement Client Acme,5000.00,TRN123456<br>
                2026-03-24,Prélèvement SARL EDF,-120.50,TRN123457
            </div>
            <p class="mt-3 text-sm text-slate-400">
                • Les montants peuvent avoir décimales ou virgules<br>
                • Les montants positifs = crédits, négatifs = débits<br>
                • L'external_id doit être unique (utilisé pour idempotence)
            </p>
        </flux:card>
    </div>
</x-app-layout>
