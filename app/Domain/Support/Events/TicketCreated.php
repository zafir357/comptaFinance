<?php

namespace App\Domain\Support\Events;

use App\Models\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * EVENT: TicketCreated
 *
 * Dispatched when a new ticket is created.
 * Listeners can perform actions like:
 * - Send notification to support team
 * - Log audit trail
 * - Update dashboard metrics
 *
 * Usage:
 *   event(new TicketCreated($ticket));
 */
class TicketCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Ticket $ticket,
    ) {}
}
