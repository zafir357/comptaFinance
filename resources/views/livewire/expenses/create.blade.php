<x-app-layout>
    <div class="max-w-2xl mx-auto space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-white">Nouvelle note de frais</h1>
            <p class="mt-2 text-slate-400">Enregistrer une dépense professionnelle</p>
        </div>

        <flux:card class="bg-slate-900 border border-slate-700">
            <form wire:submit="create" class="space-y-6">
                {{-- Category --}}
                <div>
                    <flux:select
                        wire:model="category"
                        label="Catégorie"
                        required
                        class="bg-slate-800 border-slate-600 text-white"
                    >
                        <option value="">Sélectionner une catégorie</option>
                        <option value="travel">Déplacements</option>
                        <option value="meals">Repas</option>
                        <option value="supplies">Fournitures</option>
                        <option value="utilities">Services</option>
                        <option value="other">Autre</option>
                    </flux:select>
                    @error('category')
                        <span class="text-red-400 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Supplier --}}
                <div>
                    <flux:input
                        wire:model="supplier"
                        type="text"
                        label="Fournisseur/Description"
                        placeholder="Ex: Restaurant, Carburant, etc."
                        required
                        class="bg-slate-800 border-slate-600 text-white placeholder-slate-500"
                    />
                    @error('supplier')
                        <span class="text-red-400 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Date --}}
                <div>
                    <flux:input
                        wire:model="date"
                        type="date"
                        label="Date"
                        required
                        class="bg-slate-800 border-slate-600 text-white"
                    />
                    @error('date')
                        <span class="text-red-400 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Amount & VAT --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:input
                            wire:model="amount"
                            type="number"
                            step="0.01"
                            label="Montant HT (€)"
                            placeholder="0.00"
                            required
                            class="bg-slate-800 border-slate-600 text-white placeholder-slate-500"
                        />
                        @error('amount')
                            <span class="text-red-400 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <flux:input
                            wire:model="vat_amount"
                            type="number"
                            step="0.01"
                            label="TVA (€)"
                            placeholder="0.00"
                            required
                            class="bg-slate-800 border-slate-600 text-white placeholder-slate-500"
                        />
                        @error('vat_amount')
                            <span class="text-red-400 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Receipt Upload --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Justificatif (PDF, JPG, PNG - max 5MB)
                    </label>
                    <flux:input
                        wire:model="receipt"
                        type="file"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="bg-slate-800 border-slate-600 text-white"
                    />
                    @if ($receipt)
                        <p class="mt-2 text-sm text-emerald-400">✓ Fichier sélectionné: {{ $receipt->getClientOriginalName() }}</p>
                    @endif
                    @error('receipt')
                        <span class="text-red-400 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex gap-2 pt-4">
                    <flux:button type="submit" variant="primary">
                        Créer la note de frais
                    </flux:button>
                    <a href="{{ route('expenses.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-600 px-4 py-2 text-slate-300 hover:bg-slate-800 transition">
                        Annuler
                    </a>
                </div>
            </form>
        </flux:card>
    </div>
</x-app-layout>
