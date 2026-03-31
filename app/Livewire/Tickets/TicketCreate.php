<?php

namespace App\Livewire\Tickets;

use App\Domain\Support\Actions\CreateTicketAction;
use App\Domain\Support\Data\TicketData;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class TicketCreate extends Component
{
    public string $subject = '';
    public string $priority = 'medium';
    public ?string $tags = null;

    protected $rules = [
        'subject' => 'required|string|min:5|max:500',
        'priority' => 'required|in:low,medium,high,urgent',
        'tags' => 'nullable|string|max:500',
    ];

    public function create()
    {
        $this->validate();

        // Create DTO from form data using fromArray()
        $data = TicketData::fromArray([
            'subject' => $this->subject,
            'priority' => $this->priority,
            'tags' => $this->tags ? explode(',', $this->tags) : null,
        ]);

        app(CreateTicketAction::class)->handle($data);

        session()->flash('success', 'Ticket de support créé avec succès!');
        return redirect()->route('tickets.index');
    }

    public function render()
    {
        return view('livewire.tickets.create');
    }
}
