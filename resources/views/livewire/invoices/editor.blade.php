<x-app-layout>
<div class="max-w-4xl mx-auto space-y-8">
    {{-- Header --}}
    <div>
        <h1 class="text-3xl font-bold text-gray-900">
            @if ($invoice)
                Éditer facture
            @else
                Nouvelle facture
            @endif
        </h1>
    </div>

    {{-- Form --}}
    <form wire:submit="save" class="space-y-8">
        {{-- Top section: Customer and dates --}}
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                {{-- Customer --}}
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
        <div class="rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Lignes de facture</h2>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-gray-200 bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Description</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Quantité</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Prix unitaire</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">TVA</th>
                            <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($lines as $index => $line)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <flux:input
                                        wire:model.live="lines.{{ $index }}.description"
                                        placeholder="Ex: Consultation"
                                        required
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    <flux:input
                                        type="number"
                                        wire:model.live="lines.{{ $index }}.quantity"
                                        step="0.01"
                                        class="text-right"
                                        required
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    <flux:input
                                        type="number"
                                        wire:model.live="lines.{{ $index }}.unit_price"
                                        step="0.01"
                                        class="text-right"
                                        required
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    <flux:select
                                        wire:model.live="lines.{{ $index }}.vat_rate"
                                        class="text-right"
                                    >
                                        <option value="0">0%</option>
                                        <option value="550">5.5%</option>
                                        <option value="2000">20%</option>
                                    </flux:select>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button
                                        type="button"
                                        wire:click="removeLine({{ $index }})"
                                        class="text-red-600 hover:text-red-700"
                                    >
                                        ✕
                                    </button>
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
                class="mt-4 flex items-center gap-2 text-blue-600 hover:text-blue-700"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Ajouter une ligne
            </button>
        </div>

        {{-- Totals --}}
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="space-y-2">
                <div class="flex justify-between text-gray-700">
                    <span>Sous-total</span>
                    <span class="font-medium">{{ number_format($totals['subtotal'], 2, ',', ' ') }}€</span>
                </div>
                <div class="flex justify-between border-t pt-2 text-gray-700">
                    <span>TVA</span>
                    <span class="font-medium">{{ number_format($totals['vat_total'], 2, ',', ' ') }}€</span>
                </div>
                <div class="flex justify-between border-t border-gray-300 pt-2 text-lg font-bold">
                    <span>Total TTC</span>
                    <span>{{ number_format($totals['total'], 2, ',', ' ') }}€</span>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3">
            <flux:button type="submit" variant="primary">
                @if ($invoice)
                    Mettre à jour
                @else
                    Créer facture
                @endif
            </flux:button>
            <a href="{{ route('invoices.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50">
                Annuler
            </a>
        </div>
    </form>
</div>
</x-app-layout>
