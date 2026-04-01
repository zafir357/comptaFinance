<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-white">Importer transactions bancaires</h1>
        <p class="mt-2 text-slate-400">Importer un CSV depuis votre banque</p>
    </div>

    {{-- Error Messages --}}
    @if (session('error'))
        <div class="rounded-lg border border-red-600 bg-red-900/30 px-4 py-3 text-red-200">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-600 bg-red-900/30 px-4 py-3 text-red-200">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Loading indicator for file upload --}}
    <div wire:loading wire:target="csvFile" class="rounded-lg border border-blue-600 bg-blue-900/30 px-4 py-3 text-blue-200">
        Chargement du fichier...
    </div>

        <flux:card class="bg-slate-900 border border-slate-700">
            <form wire:submit="import" class="space-y-6">
                {{-- File Upload --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Fichier CSV
                    </label>
                    <input
                        wire:model.live="csvFile"
                        type="file"
                        accept=".csv,.txt"
                        class="block w-full text-sm text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 bg-slate-800 border border-slate-600 rounded-lg cursor-pointer"
                    />
                    <p class="mt-2 text-xs text-slate-500">
                        Format: date,description,amount,external_id
                    </p>
                    @error('csvFile') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-3">
                    <flux:button 
                        type="button" 
                        wire:click="preview" 
                        variant="filled" 
                        wire:loading.attr="disabled"
                        wire:target="preview,csvFile"
                    >
                        <span wire:loading.remove wire:target="preview">Aperçu</span>
                        <span wire:loading wire:target="preview">Chargement...</span>
                    </flux:button>
                    
                    <flux:button 
                        type="submit" 
                        variant="primary" 
                        wire:loading.attr="disabled"
                        wire:target="import"
                    >
                        <span wire:loading.remove wire:target="import">Importer directement</span>
                        <span wire:loading wire:target="import">Importation...</span>
                    </flux:button>
                </div>

                {{-- Preview Table --}}
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
                    </div>
                @endif
            </form>
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
        
        {{-- CLI Alternative --}}
        <flux:card class="bg-slate-900 border border-slate-700">
            <h3 class="font-semibold text-white mb-3">Alternative: Import via CLI</h3>
            <div class="bg-slate-800 p-3 rounded text-sm font-mono text-slate-300 overflow-x-auto border border-slate-600">
                php artisan banking:import csv/bank_transactions.csv
            </div>
            <p class="mt-3 text-sm text-slate-400">
                Exécutez cette commande depuis le terminal pour importer directement.
            </p>
        </flux:card>
    </div>
</div>
