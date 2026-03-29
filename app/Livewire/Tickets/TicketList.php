<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use Livewire\Component;
use Livewire\WithPagination;

class TicketList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $priority = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Ticket::query();

        if ($this->search) {
            $query->where('subject', 'like', "%{$this->search}%");
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->priority) {
            $query->where('priority', $this->priority);
        }

        $tickets = $query->latest()->paginate(15);

        return view('livewire.tickets.list', [
            'tickets' => $tickets,
        ]);
    }
}
