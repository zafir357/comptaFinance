<?php

namespace App\Domain\Support\Actions;

use App\Domain\Support\Data\TicketMessageData;
use App\Domain\Support\Events\TicketReplied;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Support\Facades\Auth;

/**
 * ACTION: ReplyToTicketAction
 *
 * Adds a reply (message) to a support ticket.
 * Single-responsibility: handles message creation workflow.
 *
 * IMPORTANT: This is where we ensure:
 * - Message is attached to ticket
 * - User is set from authenticated user
 * - Non-internal replies dispatch TicketReplied event
 * - Message validation occurs
 *
 * Usage:
 *   $data = TicketMessageData::fromArray($request->validated());
 *   $message = app(ReplyToTicketAction::class)->handle($ticket, $data);
 */
class ReplyToTicketAction
{
    /**
     * Add a reply to a ticket.
     *
     * @param Ticket $ticket
     * @param TicketMessageData $data
     * @return TicketMessage
     */
    public function handle(Ticket $ticket, TicketMessageData $data): TicketMessage
    {
        // 1. Validate message data
        if (!$data->isValid()) {
            throw new \InvalidArgumentException('Ticket message body cannot be empty');
        }

        // 2. Create message
        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'body' => $data->body,
            'is_internal' => $data->is_internal,
        ]);

        // 3. Dispatch event only for non-internal replies
        if (!$data->is_internal) {
            event(new TicketReplied($ticket, $message));
        }

        // 4. Return with relations
        return $message->load('user', 'ticket');
    }
}
