{{-- VUE BLADE: Créer une facture --}}
<div class="p-6">
    <div class="mb-6">
        <flux:heading size="xl">Nouvelle facture</flux:heading>
        <flux:subheading class="mt-1">Créez une nouvelle facture pour un client</flux:subheading>
    </div>

    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- LEFT COLUMN: Main form --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Client & Dates --}}
                <flux:card>
                    <flux:heading size="lg" class="mb-4">Informations</flux:heading>
                    <div class="space-y-4">
                        <flux:select wire:model="customer_id" label="Client *" placeholder="Sélectionnez un client">
                            @foreach($this->customers as $customer)
                                <flux:select.option value="{{ $customer->id }}">{{ $customer->name }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <div class="grid grid-cols-2 gap-4">
                            <flux:input wire:model="issue_date" type="date" label="Date d'émission *" />
                            <flux:input wire:model="due_date" type="date" label="Date d'échéance *" />
                        </div>

                        <flux:textarea wire:model="notes" label="Notes" placeholder="Notes ou conditions particulières..." rows="2" />
                    </div>
                </flux:card>

                {{-- Lines --}}
                <flux:card>
                    <div class="flex justify-between items-center mb-4">
                        <flux:heading size="lg">Lignes de facturation</flux:heading>
                    </div>

                    <div class="space-y-3">
                        <div class="grid grid-cols-12 gap-2 text-xs font-medium text-gray-500 uppercase px-1">
                            <div class="col-span-5">Description</div>
                            <div class="col-span-2 text-right">Qté</div>
                            <div class="col-span-2 text-right">Prix HT (€)</div>
                            <div class="col-span-2 text-right">TVA %</div>
                            <div class="col-span-1"></div>
                        </div>

                        @foreach($this->lines as $index => $line)
                            <div class="grid grid-cols-12 gap-2 items-start" wire:key="line-{{ $index }}">
                                <div class="col-span-5">
                                    <flux:input wire:model="lines.{{ $index }}.description" placeholder="Description du service..." />
                                </div>
                                <div class="col-span-2">
                                    <flux:input wire:model="lines.{{ $index }}.quantity" type="number" step="0.01" min="0.01" placeholder="1" />
                                </div>
                                <div class="col-span-2">
                                    <flux:input wire:model="lines.{{ $index }}.unit_price" type="number" step="0.01" min="0" placeholder="0.00" />
                                </div>
                                <div class="col-span-2">
                                    <flux:input wire:model="lines.{{ $index }}.vat_rate" type="number" step="0.01" min="0" max="100" placeholder="20" />
                                </div>
                                <div class="col-span-1 flex justify-center pt-1">
                                    @if(count($this->lines) > 1)
                                        <flux:button type="button" wire:click="removeLine({{ $index }})" variant="ghost" size="sm" icon="trash">
                                        </flux:button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        <flux:button type="button" wire:click="addLine" variant="ghost" size="sm">
                            <flux:icon.plus class="size-4" />
                            Ajouter une ligne
                        </flux:button>
                    </div>
                </flux:card>
            </div>

            {{-- RIGHT COLUMN: Summary --}}
            <div class="space-y-6">
                <flux:card>
                    <flux:heading size="lg" class="mb-4">Récapitulatif</flux:heading>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Sous-total HT</span>
                            <span>{{ number_format($this->subtotal, 2, ',', ' ') }} €</span>
                        </div>
                        <flux:separator />
                        <div class="flex justify-between font-semibold">
                            <span>Total TTC</span>
                            <span class="text-lg">{{ number_format($this->totalWithVat, 2, ',', ' ') }} €</span>
                        </div>
                    </div>
                </flux:card>

                <div class="flex flex-col gap-3">
                    <flux:button type="submit" variant="primary" class="w-full">
                        <flux:icon.check class="size-5" />
                        Créer la facture
                    </flux:button>
                    <flux:button href="{{ route('invoices.index') }}" variant="ghost" class="w-full" wire:navigate>
                        Annuler
                    </flux:button>
                </div>
            </div>
        </div>
    </form>
</div>
