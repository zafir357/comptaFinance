<div class="max-w-4xl mx-auto space-y-8">
    {{-- Header --}}
    <div>
        <h1 class="text-3xl font-bold text-white">
            @if ($invoice) Éditer la facture {{ $invoice->number }} @else Nouvelle facture @endif
        </h1>
        <p class="mt-1 text-sm text-slate-400">
            @if ($invoice) Modifiez les informations de la facture @else Remplissez les informations pour créer une facture @endif
        </p>
    </div>

    {{-- Form --}}
    <form wire:submit="save" class="space-y-8">
        {{-- Top section: Customer and dates --}}
        <div class="rounded-lg bg-slate-900 border border-slate-700 p-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                {{-- Customer --}}
                <div>
                    <flux:select
                        wire:model="customer_id"
                        label="Client"
                        required
                    >
                        <option value="">Sélectionner un client</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </flux:select>
                    @error('customer_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Dates --}}
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <flux:input
                        type="date"
                        wire:model="issue_date"
                        label="Date d'émission"
                        required
                    />
                    <flux:input
                        type="date"
                        wire:model="due_date"
                        label="Date d'échéance"
                        required
                    />
                </div>
            </div>

            {{-- Notes --}}
            <div class="mt-6">
                <flux:textarea
                    wire:model="notes"
                    label="Notes"
                    placeholder="Notes internes ou pour le client..."
                    rows="3"
                />
            </div>
        </div>

        {{-- Line items --}}
        <div class="rounded-lg bg-slate-900 border border-slate-700 p-6">
            <h2 class="mb-4 text-lg font-semibold text-white">Lignes de facture</h2>

            @error('lines')
                <p class="mb-4 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-slate-700 bg-slate-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-medium text-white">Description</th>
                            <th class="w-28 px-4 py-3 text-right text-sm font-medium text-white">Qté</th>
                            <th class="w-36 px-4 py-3 text-right text-sm font-medium text-white">Prix unitaire HT</th>
                            <th class="w-28 px-4 py-3 text-right text-sm font-medium text-white">TVA</th>
                            <th class="w-12 px-4 py-3 text-center text-sm font-medium text-white"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @foreach ($lines as $index => $line)
                            <tr class="hover:bg-slate-800">
                                <td class="px-4 py-3">
                                    <flux:input
                                        wire:model.live="lines.{{ $index }}.description"
                                        placeholder="Ex: Consultation Laravel"
                                        required
                                    />
                                    @error("lines.{$index}.description")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-3">
                                    <flux:input
                                        type="number"
                                        wire:model.live="lines.{{ $index }}.quantity"
                                        step="0.01"
                                        min="0.01"
                                        required
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    <flux:input
                                        type="number"
                                        wire:model.live="lines.{{ $index }}.unit_price"
                                        step="0.01"
                                        min="0"
                                        required
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    {{-- French VAT rates: 0%, 5.5%, 10%, 20% --}}
                                    <flux:select wire:model.live="lines.{{ $index }}.vat_rate">
                                        <option value="0">0 %</option>
                                        <option value="550">5,5 %</option>
                                        <option value="1000">10 %</option>
                                        <option value="2000">20 %</option>
                                    </flux:select>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if (count($lines) > 1)
                                        <button
                                            type="button"
                                            wire:click="removeLine({{ $index }})"
                                            class="text-red-400 hover:text-red-600"
                                            title="Supprimer cette ligne"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Add line button --}}
            <button
                type="button"
                wire:click="addLine"
                class="mt-4 flex items-center gap-2 text-blue-600 hover:text-blue-700 text-sm font-medium"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Ajouter une ligne
            </button>
        </div>

        {{-- Totals --}}
        <div class="rounded-lg bg-slate-900 border border-slate-700 p-6">
            <div class="ml-auto max-w-xs space-y-2">
                <div class="flex justify-between text-slate-300">
                    <span>Sous-total HT</span>
                    <span class="font-medium">{{ number_format($totals['subtotal'], 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between border-t border-slate-700 pt-2 text-slate-300">
                    <span>TVA</span>
                    <span class="font-medium">{{ number_format($totals['vat_total'], 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between border-t border-slate-700 pt-2 text-xl font-bold text-white">
                    <span>Total TTC</span>
                    <span class="text-blue-500">{{ number_format($totals['total'], 2, ',', ' ') }} €</span>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3">
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                <span wire:loading.remove>
                    @if ($invoice) Mettre à jour @else Créer la facture @endif
                </span>
                <span wire:loading>Enregistrement...</span>
            </flux:button>
            <a href="{{ route('invoices.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-700 px-4 py-2 text-slate-300 hover:bg-slate-800">
                Annuler
            </a>
        </div>
    </form>
</div>
