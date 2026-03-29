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
 * EVENT: ExpenseReceiptProcessed
 *
 * Fired when an expense receipt has been processed (successfully or failed).
 * Listeners can react to:
 * - Update receipt metadata
 * - Send notifications to user
 * - Trigger OCR/extraction workflows
 * - Log processing result
 *
 * Usage:
 *   event(new ExpenseReceiptProcessed($expense, $success, $result));
 */
class ExpenseReceiptProcessed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Expense $expense,
        public bool $success = true,
        public ?array $result = null,
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
            'receipt_status' => $this->expense->receipt_status,
            'success' => $this->success,
            'processed_at' => $this->expense->receipt_processed_at?->toIso8601String(),
        ];
    }
}
