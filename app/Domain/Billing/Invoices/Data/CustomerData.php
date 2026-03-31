<?php

namespace App\Domain\Billing\Invoices\Data;

/**
 * DTO: CustomerData
 *
 * Type-safe data container for customer creation/updates.
 * Encapsulates all customer attributes.
 *
 * Usage:
 *   $data = CustomerData::fromArray($request->validated());
 *   $customer = app(CreateCustomerAction::class)->handle($data);
 */
class CustomerData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone = null,
        public ?string $address = null,
        public ?string $tax_number = null,
        public ?string $notes = null,
    ) {}

    /**
     * Create from form request data or array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            tax_number: $data['tax_number'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }

    /**
     * Validate email format.
     */
    public function hasValidEmail(): bool
    {
        return filter_var($this->email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Get display name for customer.
     */
    public function displayName(): string
    {
        return $this->name;
    }
}
