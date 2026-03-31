<x-layouts.app>
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">{{ $ticket->subject }}</h1>
                <div class="mt-2 flex items-center gap-3">
                    <flux:badge
                        @if ($ticket->priority === 'urgent') color="red"
                        @elseif ($ticket->priority === 'high') color="orange"
                        @elseif ($ticket->priority === 'medium') color="yellow"
                        @else color="gray"
                        @endif
                    >
                        {{ ucfirst($ticket->priority) }}
                    </flux:badge>
                    <flux:badge
                        @if ($ticket->status === 'open') color="blue"
                        @elseif ($ticket->status === 'waiting') color="yellow"
                        @else color="green"
                        @endif
                    >
                        {{ $ticket->status === 'open' ? 'Ouvert' : ($ticket->status === 'waiting' ? 'En attente' : 'Fermé') }}
                    </flux:badge>
                    <span class="text-sm text-slate-400">Créé {{ $ticket->created_at->diffForHumans() }}</span>
                </div>
            </div>
            <a href="{{ route('tickets.index') }}" class="rounded-lg border border-slate-700 px-4 py-2 text-slate-300 hover:bg-slate-800">
                Retour
            </a>
        </div>

        {{-- Messages --}}
        <div class="space-y-4 max-h-96 overflow-y-auto">
            @foreach ($ticket->messages as $message)
                <div class="rounded-lg {{ $message->is_internal ? 'bg-yellow-900/30 border-l-4 border-yellow-600' : 'bg-slate-800' }} p-4 border border-slate-700">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-medium text-white">{{ $message->user->name }}</p>
                            <p class="text-xs text-slate-400">{{ $message->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @if ($message->is_internal)
                            <flux:badge color="yellow">Interne</flux:badge>
                        @endif
                    </div>
                    <p class="mt-3 text-slate-300 whitespace-pre-wrap">{{ $message->body }}</p>
                </div>
            @endforeach
        </div>

        {{-- Reply Form --}}
        @if ($ticket->status !== 'closed')
            <flux:card>
                <h3 class="font-semibold text-white mb-3">Ajouter une réponse</h3>

                <form wire:submit="send" class="space-y-4">
                    <flux:textarea
                        wire:model="reply"
                        placeholder="Votre réponse..."
                        rows="4"
                    />

                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            wire:model="internal"
                            id="internal"
                            class="rounded bg-slate-800 border-slate-600"
                        />
                        <label for="internal" class="text-sm text-slate-300">
                            Marquer comme interne (non visible au client)
                        </label>
                    </div>

                    <div class="flex gap-2">
                        <flux:button type="submit" variant="primary">
                            Envoyer
                        </flux:button>
                        <flux:button
                            type="button"
                            wire:click="close"
                            variant="secondary"
                        >
                            Fermer le ticket
                        </flux:button>
                    </div>
                </form>
            </flux:card>
        @endif
    </div>
</x-layouts.app>
