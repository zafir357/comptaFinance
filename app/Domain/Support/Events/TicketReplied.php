<?php

namespace App\Domain\Support\Events;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * EVENT: TicketReplied
 *
 * Dispatched when a reply is added to a ticket (non-internal).
 * Listeners can perform actions like:
 * - Send notification to customer/staff
 * - Update ticket last_response_at timestamp
 * - Increment reply count metrics
 *
 * Usage:
 *   event(new TicketReplied($ticket, $message));
 */
class TicketReplied
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public TicketMessage $message,
    ) {}
}
