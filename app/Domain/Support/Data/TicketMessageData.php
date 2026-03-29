<?php

namespace App\Domain\Support\Data;

/**
 * DTO: TicketMessageData
 *
 * Type-safe data container for ticket message creation.
 * Encapsulates message attributes.
 *
 * Usage:
 *   $data = TicketMessageData::fromArray($request->validated());
 *   $message = app(ReplyToTicketAction::class)->handle($ticket, $data);
 */
class TicketMessageData
{
    public function __construct(
        public string $body,
        public bool $is_internal = false,
    ) {}

    /**
     * Create from form request data or array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            body: $data['body'],
            is_internal: $data['is_internal'] ?? false,
        );
    }

    /**
     * Validate message body is not empty.
     */
    public function isValid(): bool
    {
        return !empty(trim($this->body));
    }
}
