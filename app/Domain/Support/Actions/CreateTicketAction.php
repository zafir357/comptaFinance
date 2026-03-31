<?php

namespace App\Domain\Support\Actions;

use App\Domain\Support\Data\TicketData;
use App\Domain\Support\Events\TicketCreated;
use App\Domain\Support\Repositories\TicketRepository;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

/**
 * ACTION: CreateTicketAction
 *
 * Creates a new support ticket.
 * Single-responsibility: handles ticket creation workflow.
 *
 * IMPORTANT: This is where we ensure:
 * - Organization is set (automatic via repository)
 * - User is set from authenticated user
 * - Status is always 'open' for new tickets
 * - TicketCreated event is dispatched
 *
 * Usage:
 *   $data = TicketData::fromArray($request->validated());
 *   $ticket = app(CreateTicketAction::class)->handle($data);
 */
class CreateTicketAction
{
    public function __construct(
        private TicketRepository $ticketRepository,
    ) {}

    /**
     * Create a new ticket.
     *
     * @param TicketData $data
     * @return Ticket
     */
    public function handle(TicketData $data): Ticket
    {
        // 1. Validate data
        if (!$data->isValidStatus()) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid ticket status: %s. Must be one of: open, waiting, closed',
                $data->status
            ));
        }

        if (!$data->isValidPriority()) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid ticket priority: %s. Must be one of: low, medium, high, urgent',
                $data->priority
            ));
        }

        // 2. Create ticket via repository
        $ticket = $this->ticketRepository->create([
            'user_id' => Auth::id(),
            'subject' => $data->subject,
            'status' => 'open',  // Always start with open
            'priority' => $data->priority,
            'tags' => $data->tags,
        ]);

        // 3. Dispatch event
        event(new TicketCreated($ticket));

        // 4. Return with relations
        return $ticket->load('user', 'organization');
    }
}
