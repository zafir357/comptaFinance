<?php

namespace App\Domain\Banking\Data;

use Carbon\Carbon;

/**
 * DTO: BankTransactionData
 *
 * Type-safe data container for bank transaction creation.
 * All money amounts are in CENTS (integer) to avoid float precision issues.
 *
 * Usage:
 *   $data = new BankTransactionData(
 *       date: Carbon::parse('2026-03-29'),
 *       description: 'Virement client',
 *       amount: 50000,  // €500.00 in cents
 *       currency: 'EUR',
 *       external_id: 'BANK-2026-123456'
 *   );
 */
class BankTransactionData
{
    public function __construct(
        public Carbon $date,
        public string $description,
        public int $amount,  // Cents (can be negative for debits)
        public string $currency = 'EUR',
        public string $external_id,
    ) {}

    /**
     * Create from CSV array
     *
     * @param array $data with keys: date, description, amount, currency, external_id
     */
    public static function fromArray(array $data): self
    {
        return new self(
            date: Carbon::parse($data['date']),
            description: (string) $data['description'],
            amount: (int) $data['amount'],  // Already in cents from parser
            currency: $data['currency'] ?? 'EUR',
            external_id: (string) $data['external_id'],
        );
    }

    /**
     * Convert amount from euros to cents
     */
    public static function amountInCents(float|int|string $amount): int
    {
        if (is_string($amount)) {
            // Handle comma as decimal separator (French format)
            $amount = str_replace(',', '.', $amount);
        }

        return (int) round((float) $amount * 100);
    }

    /**
     * Get amount formatted as euros
     */
    public function getAmountInEuros(): string
    {
        return number_format($this->amount / 100, 2, ',', ' ');
    }

    /**
     * Is this a credit (income)?
     */
    public function isCredit(): bool
    {
        return $this->amount > 0;
    }

    /**
     * Is this a debit (expense)?
     */
    public function isDebit(): bool
    {
        return $this->amount < 0;
    }
}
