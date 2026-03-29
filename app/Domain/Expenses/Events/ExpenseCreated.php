<?php

namespace App\Domain\Expenses\Events;

use App\Models\Expense;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * EVENT: ExpenseCreated
 *
 * Fired when a new expense is created.
 * Listeners can react to:
 * - Send notifications
 * - Log to audit trail
 * - Trigger side effects
 *
 * Usage:
 *   event(new ExpenseCreated($expense));
 */
class ExpenseCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Expense $expense,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('organization.' . $this->expense->organization_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->expense->id,
            'category' => $this->expense->category,
            'supplier' => $this->expense->supplier,
            'amount' => $this->expense->amount,
            'date' => $this->expense->date->toIso8601String(),
        ];
    }
}
