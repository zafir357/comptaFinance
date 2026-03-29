<?php

namespace App\Domain\Support\Events;

use App\Models\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * EVENT: TicketClosed
 *
 * Dispatched when a ticket is closed.
 * Listeners can perform actions like:
 * - Send confirmation to customer
 * - Calculate resolution time metrics
 * - Archive ticket in external system
 *
 * Usage:
 *   event(new TicketClosed($ticket));
 */
class TicketClosed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Ticket $ticket,
    ) {}
}
