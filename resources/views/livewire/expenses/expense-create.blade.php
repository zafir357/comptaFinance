{{-- VUE BLADE: Créer une dépense --}}
<div class="p-6">
    <div class="mb-6">
        <flux:heading size="xl">Nouvelle dépense</flux:heading>
        <flux:subheading class="mt-1">Enregistrez une dépense ou note de frais</flux:subheading>
    </div>

    <flux:card class="max-w-2xl">
        <form wire:submit="save">
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="date" type="date" label="Date *" />

                    <flux:select wire:model="category" label="Catégorie *" placeholder="Sélectionnez une catégorie">
                        @foreach($commonCategories as $cat)
                            <flux:select.option value="{{ $cat }}">{{ $cat }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:input wire:model="supplier" label="Fournisseur *" placeholder="Ex: Total Énergie, Amazon Business..." />

                <div class="grid grid-cols-2 gap-4">
                    <flux:input
                        wire:model="amount"
                        type="number"
                        step="0.01"
                        min="0"
                        label="Montant HT (€) *"
                        placeholder="0,00"
                    />
                    <flux:input
                        wire:model="vat_amount"
                        type="number"
                        step="0.01"
                        min="0"
                        label="Montant TVA (€)"
                        placeholder="0,00"
                    />
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <flux:button href="{{ route('expenses.index') }}" variant="ghost" wire:navigate>
                    Annuler
                </flux:button>
                <flux:button type="submit" variant="primary">
                    <flux:icon.check class="size-5" />
                    Enregistrer
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>
