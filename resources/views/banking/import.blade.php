<x-layouts.app>
    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-white">Importer transactions bancaires</h1>
            <p class="mt-2 text-slate-400">Importer un fichier CSV depuis votre banque</p>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="rounded-lg border border-red-600 bg-red-900/30 px-4 py-3 text-red-200">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- Upload Form --}}
        <flux:card>
            <form action="{{ route('banking.preview') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Fichier CSV</label>
                    <input 
                        type="file" 
                        name="csv_file" 
                        accept=".csv,.txt"
                        required
                        class="block w-full text-sm text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 bg-slate-800 border border-slate-600 rounded-lg cursor-pointer"
                    />
                    <p class="mt-2 text-xs text-slate-500">Format: date,description,amount,external_id</p>
                </div>
                <flux:button type="submit" variant="filled">Aperçu</flux:button>
            </form>
        </flux:card>

        {{-- Preview Table --}}
        @if (isset($preview) && count($preview) > 0)
            <flux:card>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-white">Aperçu ({{ count($preview) }} sur {{ $total }})</h2>
                        <form action="{{ route('banking.import.store') }}" method="POST">
                            @csrf
                            <flux:button type="submit" variant="primary">
                                Importer {{ $total }} transactions
                            </flux:button>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs uppercase bg-slate-800 text-slate-400">
                                <tr>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3">Description</th>
                                    <th class="px-4 py-3 text-right">Montant</th>
                                    <th class="px-4 py-3">ID Externe</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                @foreach ($preview as $tx)
                                    <tr class="bg-slate-900">
                                        <td class="px-4 py-3 text-slate-300">{{ $tx->date->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-slate-300">{{ $tx->description }}</td>
                                        <td class="px-4 py-3 text-right font-medium {{ $tx->amount >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                            {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount / 100, 2, ',', ' ') }} €
                                        </td>
                                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $tx->external_id }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </flux:card>
        @endif

        {{-- Help --}}
        <flux:card>
            <h3 class="font-semibold text-white mb-3">Format du fichier CSV</h3>
            <pre class="bg-slate-800 p-3 rounded text-sm font-mono text-slate-300 overflow-x-auto border border-slate-600">date,description,amount,external_id
2026-03-25,Virement Client Acme,5000.00,TRN123456
2026-03-24,Prélèvement SARL EDF,-120.50,TRN123457</pre>
            <ul class="mt-3 text-sm text-slate-400 space-y-1">
                <li>• Montants positifs = crédits, négatifs = débits</li>
                <li>• external_id doit être unique (idempotence)</li>
            </ul>
        </flux:card>
    </div>
</x-layouts.app>
