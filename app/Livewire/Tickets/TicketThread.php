<?php

namespace App\Livewire\Tickets;

use App\Domain\Support\Actions\ReplyToTicketAction;
use App\Domain\Support\Data\TicketMessageData;
use App\Models\Ticket;
use Livewire\Component;

class TicketThread extends Component
{
    public Ticket $ticket;
    public string $reply = '';
    public bool $internal = false;

    public function mount(Ticket $ticket)
    {
        $this->ticket = $ticket->load('messages');
    }

    public function send()
    {
        $this->validate([
            'reply' => 'required|string|min:5|max:5000',
        ]);

        $data = new TicketMessageData(
            body: $this->reply,
            is_internal: $this->internal,
        );

        app(ReplyToTicketAction::class)->handle($this->ticket, $data);

        // Reload ticket with new messages
        $this->ticket = $this->ticket->fresh('messages');
        $this->reply = '';
        $this->internal = false;

        session()->flash('success', 'Réponse envoyée!');
    }

    public function close()
    {
        $this->ticket->update(['status' => 'closed']);
        session()->flash('success', 'Ticket ferméé');
        return redirect()->route('tickets.index');
    }

    public function render()
    {
        return view('livewire.tickets.thread');
    }
}
