<x-app-layout>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">Support - Tickets</h1>
                <p class="mt-2 text-sm text-slate-400">Gérez vos demandes de support</p>
            </div>
            <a href="{{ route('tickets.create') }}" class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nouveau ticket
            </a>
        </div>

        {{-- Filters --}}
        <div class="space-y-4 rounded-lg bg-slate-900 border border-slate-700 p-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <flux:input
                    wire:model.live="search"
                    type="search"
                    placeholder="Rechercher un sujet..."
                    icon="magnifying-glass"
                />
                <flux:select
                    wire:model.live="status"
                >
                    <option value="">Tous les statuts</option>
                    <option value="open">Ouvert</option>
                    <option value="waiting">En attente</option>
                    <option value="closed">Fermé</option>
                </flux:select>
                <flux:select
                    wire:model.live="priority"
                >
                    <option value="">Toutes les priorités</option>
                    <option value="low">Basse</option>
                    <option value="medium">Moyenne</option>
                    <option value="high">Haute</option>
                    <option value="urgent">Urgent</option>
                </flux:select>
            </div>
        </div>

        {{-- Tickets --}}
        <div class="space-y-3">
            @forelse ($tickets as $ticket)
                <flux:card>
                    <a href="{{ route('tickets.show', $ticket) }}" class="block hover:bg-slate-800">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-medium text-white">{{ $ticket->subject }}</h3>
                                <p class="mt-1 text-sm text-slate-400">
                                    {{ count($ticket->messages) }} message(s) • {{ $ticket->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                @if ($ticket->priority === 'urgent')
                                    <flux:badge color="red">Urgent</flux:badge>
                                @elseif ($ticket->priority === 'high')
                                    <flux:badge color="orange">Haute</flux:badge>
                                @elseif ($ticket->priority === 'medium')
                                    <flux:badge color="yellow">Moyenne</flux:badge>
                                @else
                                    <flux:badge color="gray">Basse</flux:badge>
                                @endif

                                @if ($ticket->status === 'open')
                                    <flux:badge color="blue">Ouvert</flux:badge>
                                @elseif ($ticket->status === 'waiting')
                                    <flux:badge color="yellow">En attente</flux:badge>
                                @else
                                    <flux:badge color="green">✓ Fermé</flux:badge>
                                @endif
                            </div>
                        </div>
                    </a>
                </flux:card>
            @empty
                <flux:card>
                    <p class="text-center py-8 text-slate-500">Aucun ticket trouvé</p>
                </flux:card>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="flex justify-center">
            {{ $tickets->links() }}
        </div>
    </div>
</x-app-layout>
