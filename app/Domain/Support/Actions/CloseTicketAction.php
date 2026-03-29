<?php

namespace App\Domain\Support\Actions;

use App\Domain\Support\Events\TicketClosed;
use App\Models\Ticket;

/**
 * ACTION: CloseTicketAction
 *
 * Closes a support ticket.
 * Single-responsibility: handles ticket closure workflow.
 *
 * IMPORTANT: This is where we ensure:
 * - Ticket status is updated to 'closed'
 * - TicketClosed event is dispatched
 * - Closed timestamp is recorded
 *
 * Usage:
 *   $ticket = app(CloseTicketAction::class)->handle($ticket);
 */
class CloseTicketAction
{
    /**
     * Close a ticket.
     *
     * @param Ticket $ticket
     * @return Ticket
     */
    public function handle(Ticket $ticket): Ticket
    {
        // 1. Check if already closed
        if ($ticket->isClosed()) {
            throw new \InvalidArgumentException('Ticket is already closed');
        }

        // 2. Update status
        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        // 3. Dispatch event
        event(new TicketClosed($ticket));

        // 4. Return refreshed ticket
        return $ticket->refresh()->load('user', 'organization');
    }
}
