<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-white">Rapprochement bancaire</h1>
            <p class="mt-2 text-sm text-slate-400">Associer transactions bancaires et factures/dépenses</p>
        </div>
        <a href="{{ route('banking.import') }}" class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Importer CSV
        </a>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-slate-700">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button 
                wire:click="$set('tab', 'unreconciled')"
                class="@if($tab === 'unreconciled') border-blue-500 text-blue-400 @else border-transparent text-slate-400 hover:border-slate-500 hover:text-slate-300 @endif whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition"
            >
                À rapprocher
            </button>
            <button 
                wire:click="$set('tab', 'reconciled')"
                class="@if($tab === 'reconciled') border-blue-500 text-blue-400 @else border-transparent text-slate-400 hover:border-slate-500 hover:text-slate-300 @endif whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition"
            >
                Rapprochées
            </button>
        </nav>
    </div>

        {{-- Search --}}
        <flux:input
            wire:model.live="search"
            type="search"
            placeholder="Chercher par description..."
            icon="magnifying-glass"
        />

        {{-- Transactions List --}}
        <div class="grid gap-4">
            @forelse ($transactions as $transaction)
                <flux:card>
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-3 w-3 rounded-full"
                                    :class="'{{ $transaction->amount >= 0 ? 'bg-green-500' : 'bg-red-500' }}'"
                                ></div>
                                <div>
                                    <p class="font-medium text-white">{{ $transaction->description }}</p>
                                    <p class="text-sm text-slate-400">{{ $transaction->date->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="text-lg font-bold {{ $transaction->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount / 100, 2, ',', ' ') }}€
                            </p>
                            @if ($transaction->reconciled)
                                <flux:badge color="green">Rapprochée</flux:badge>
                            @else
                                <flux:badge color="yellow">À rapprocher</flux:badge>
                            @endif
                        </div>
                    </div>

                    {{-- Selection Modal for Unreconciled --}}
                    @if (!$transaction->reconciled && $selectedTransactionId === $transaction->id)
                        <div class="mt-4 border-t border-slate-700 pt-4">
                            <p class="text-sm font-medium text-slate-300 mb-2">Associer à une facture/dépense:</p>
                            <flux:select
                                wire:model="selectedInvoiceId"
                            >
                                <option value="">-- Sélectionner --</option>
                                @foreach ($invoices as $invoice)
                                    <option value="{{ $invoice->id }}">
                                        {{ $invoice->number }} - {{ $invoice->customer->name }} ({{ number_format($invoice->total / 100, 2, ',', ' ') }}€)
                                    </option>
                                @endforeach
                            </flux:select>

                            <div class="mt-2 flex gap-2">
                                <flux:button
                                    wire:click="reconcile"
                                    wire:disabled="!selectedInvoiceId"
                                    variant="primary"
                                    size="sm"
                                >
                                    Rapprocher
                                </flux:button>
                                <flux:button
                                    wire:click="resetSelection"
                                    variant="ghost"
                                    size="sm"
                                >
                                    Annuler
                                </flux:button>
                            </div>
                        </div>
                    @elseif (!$transaction->reconciled)
                        <div class="mt-4 pt-2">
                            <flux:button
                                wire:click="$set('selectedTransactionId', {{ $transaction->id }})"
                                variant="filled"
                                size="sm"
                            >
                                Rapprocher
                            </flux:button>
                        </div>
                    @endif
                </flux:card>
            @empty
                <flux:card>
                    <p class="text-center py-8 text-slate-500">
                        @if ($tab === 'unreconciled')
                            Aucune transaction à rapprocher
                        @else
                            Aucune transaction rapprochée
                        @endif
                    </p>
                </flux:card>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="flex justify-center">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
