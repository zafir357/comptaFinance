<?php

namespace App\Domain\Expenses\Data;

use Carbon\Carbon;

/**
 * DTO: ExpenseData
 *
 * Type-safe data container for expense creation.
 * All money amounts are in CENTS (integer).
 *
 * Usage:
 *   $data = ExpenseData::fromArray($request->validated());
 *   $expense = CreateExpenseAction::handle($data);
 */
class ExpenseData
{
    public function __construct(
        public string $category,
        public string $supplier,
        public int $amount,              // Cents
        public int $vat_amount,          // Cents
        public Carbon $date,
        public ?string $receipt_path = null,
        public ?string $receipt_status = null,
        public ?string $notes = null,
    ) {}

    /**
     * Create from form request data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            category: $data['category'],
            supplier: $data['supplier'],
            amount: (int) ($data['amount'] * 100),        // Convert euros to cents
            vat_amount: (int) ($data['vat_amount'] * 100), // Convert euros to cents
            date: Carbon::parse($data['date']),
            receipt_path: $data['receipt_path'] ?? null,
            receipt_status: $data['receipt_path'] ? 'pending' : null,
            notes: $data['notes'] ?? null,
        );
    }

    /**
     * Get total amount (amount + VAT) in cents.
     */
    public function total(): int
    {
        return $this->amount + $this->vat_amount;
    }

    /**
     * Get total amount in euros for display.
     */
    public function totalInEuros(): string
    {
        return number_format($this->total() / 100, 2);
    }

    /**
     * Validate category is one of allowed values.
     */
    public static function validCategories(): array
    {
        return [
            'travel',
            'meals',
            'supplies',
            'utilities',
            'maintenance',
            'software',
            'other',
        ];
    }
}
