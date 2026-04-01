<div class="space-y-6">
    {{-- Header --}}
    <div class="relative mb-5">
        <flux:heading size="xl">Rapprochement bancaire</flux:heading>
        <flux:subheading>Associer transactions bancaires et factures/dépenses</flux:subheading>
    </div>

    <div class="flex items-center justify-between">
        {{-- Tab Buttons --}}
        <div class="flex gap-2">
            <flux:button 
                wire:click="$set('tab', 'unreconciled')"
                variant="{{ $tab === 'unreconciled' ? 'filled' : 'ghost' }}"
                size="sm"
            >
                À rapprocher
            </flux:button>
            <flux:button 
                wire:click="$set('tab', 'reconciled')"
                variant="{{ $tab === 'reconciled' ? 'filled' : 'ghost' }}"
                size="sm"
            >
                Rapprochées
            </flux:button>
        </div>

        <flux:button href="{{ route('banking.import') }}" variant="primary" icon="arrow-up-tray">
            Importer CSV
        </flux:button>
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
                            <div class="h-3 w-3 rounded-full {{ $transaction->amount >= 0 ? 'bg-green-500' : 'bg-red-500' }}"></div>
                            <div>
                                <p class="font-medium text-white">{{ $transaction->description }}</p>
                                <p class="text-sm text-zinc-400">{{ $transaction->date->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <p class="text-lg font-bold {{ $transaction->amount >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount / 100, 2, ',', ' ') }} €
                            </p>
                            @if ($transaction->reconciled)
                                <flux:badge color="green" size="sm">Rapprochée</flux:badge>
                            @else
                                <flux:badge color="yellow" size="sm">À rapprocher</flux:badge>
                            @endif
                        </div>

                        @if (!$transaction->reconciled)
                            <flux:modal.trigger name="reconcile-modal">
                                <flux:button
                                    wire:click="selectTransaction({{ $transaction->id }})"
                                    variant="filled"
                                    size="sm"
                                >
                                    Rapprocher
                                </flux:button>
                            </flux:modal.trigger>
                        @endif
                    </div>
                </div>
            </flux:card>
        @empty
            <flux:card>
                <p class="text-center py-8 text-zinc-500">
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

    {{-- Reconciliation Modal --}}
    <flux:modal name="reconcile-modal" wire:model.self="showReconcileModal" :key="'reconcile-modal-' . ($selectedTransactionId ?? 'none')" class="max-w-lg" wire:close="resetReconciliation">
        <form wire:submit="reconcile" class="space-y-6">
            <div>
                <flux:heading size="lg">Rapprocher une transaction</flux:heading>
                <flux:subheading>Associer à une facture</flux:subheading>
            </div>

            @if ($selectedTransaction)
                {{-- Transaction Info --}}
                <div class="rounded-lg bg-zinc-800 border border-zinc-700 p-4 space-y-3">
                    <div>
                        <p class="text-xs text-zinc-500 uppercase">Description</p>
                        <p class="font-medium text-white mt-1">{{ $selectedTransaction->description }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500 uppercase">Montant</p>
                        <p class="text-lg font-bold {{ $selectedTransaction->amount >= 0 ? 'text-green-400' : 'text-red-400' }} mt-1">
                            {{ $selectedTransaction->amount >= 0 ? '+' : '' }}{{ number_format($selectedTransaction->amount / 100, 2, ',', ' ') }} €
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500 uppercase">Date</p>
                        <p class="text-zinc-300 mt-1">{{ $selectedTransaction->date->format('d/m/Y') }}</p>
                    </div>
                </div>

                {{-- Invoice Selection --}}
                <flux:select wire:model="selectedInvoiceId" label="Facture (négative)" placeholder="Sélectionner une facture négative...">
                    @foreach ($invoices as $invoice)
                        <flux:select.option value="{{ $invoice->id }}">
                            {{ $invoice->number }} - {{ $invoice->customer->name ?? 'N/A' }} ({{ number_format($invoice->total / 100, 2, ',', ' ') }} €) | Restant: {{ number_format($invoice->remaining_amount / 100, 2, ',', ' ') }} €
                        </flux:select.option>
                    @endforeach
                </flux:select>
                @error('selectedInvoiceId') 
                    <p class="text-red-400 text-sm">{{ $message }}</p>
                @enderror

                {{-- Applied Amount --}}
                <flux:input
                    wire:model="appliedAmount"
                    type="number"
                    step="0.01"
                    label="Montant appliqué (€/négatif)"
                    placeholder="-100,00"
                />
                @error('appliedAmount') 
                    <p class="text-red-400 text-sm">{{ $message }}</p>
                @enderror
            @else
                <p class="text-zinc-400 text-center py-4">Sélectionnez une transaction...</p>
            @endif

            {{-- Actions --}}
            <div class="flex justify-end space-x-2">
                <flux:modal.close>
                    <flux:button variant="filled">Annuler</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Confirmer</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
