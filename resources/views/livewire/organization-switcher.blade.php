<div class="space-y-2">
    <label class="block text-xs font-semibold text-gray-600 uppercase">Organisation</label>

    <flux:select
        wire:model="selectedOrgId"
        wire:change="switchOrganization($event.target.value)"
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
        <div class="mt-2 text-xs text-gray-600">
            <p><strong>{{ $currentOrg->name }}</strong></p>
            <p class="mt-1">Rôle:
                <span class="font-medium">
                    @php
                        $role = auth()->user()->roleInOrganization($currentOrg->id);
                    @endphp
                    {{ ucfirst($role ?? 'N/A') }}
                </span>
            </p>
        </div>
    @endif
</div>
