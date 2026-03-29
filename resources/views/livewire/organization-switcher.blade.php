<div class="space-y-2">
    <label class="block text-xs font-semibold text-slate-400 uppercase">Organisation</label>

    <flux:select
        wire:model="selectedOrgId"
        wire:change="switchOrganization($event.target.value)"
        class="bg-slate-800 border-slate-600 text-white"
    >
        @forelse ($organizations as $org)
            <option value="{{ $org->id }}" @selected($org->id === $currentOrg?->id)>
                {{ $org->name }}
            </option>
        @empty
            <option value="">Aucune organisation</option>
        @endforelse
    </flux:select>

    @if ($currentOrg)
        <div class="mt-2 text-xs text-slate-300">
            <p><strong class="text-white">{{ $currentOrg->name }}</strong></p>
            <p class="mt-1">Rôle:
                <span class="font-medium text-slate-200">
                    @php
                        $role = auth()->user()->roleInOrganization($currentOrg->id);
                    @endphp
                    {{ ucfirst($role ?? 'N/A') }}
                </span>
            </p>
        </div>
    @endif
</div>
