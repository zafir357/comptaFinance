<?php

namespace App\Domain\Support\Data;

/**
 * DTO: TicketData
 *
 * Type-safe data container for ticket creation/updates.
 * Encapsulates all ticket attributes to be stored.
 *
 * Usage:
 *   $data = TicketData::fromArray($request->validated());
 *   $ticket = app(CreateTicketAction::class)->handle($data);
 */
class TicketData
{
    public function __construct(
        public string $subject,
        public string $status = 'open',
        public string $priority = 'medium',
        public ?array $tags = null,
    ) {}

    /**
     * Create from form request data or array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            subject: $data['subject'],
            status: $data['status'] ?? 'open',
            priority: $data['priority'] ?? 'medium',
            tags: $data['tags'] ?? null,
        );
    }

    /**
     * Validate status is one of allowed values.
     */
    public function isValidStatus(): bool
    {
        return in_array($this->status, ['open', 'waiting', 'closed']);
    }

    /**
     * Validate priority is one of allowed values.
     */
    public function isValidPriority(): bool
    {
        return in_array($this->priority, ['low', 'medium', 'high', 'urgent']);
    }
}
