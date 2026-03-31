<?php

namespace App\Domain\Banking\Data;

/**
 * DTO: ReconciliationData
 *
 * Type-safe data container for bank transaction reconciliation.
 * Encapsulates reconciliation attributes.
 *
 * Usage:
 *   $data = ReconciliationData::fromArray($request->validated());
 *   $reconciliation = app(ReconcileTransactionAction::class)->handle($transaction, $reconcilable, $data);
 */
class ReconciliationData
{
    public function __construct(
        public string $reconcilable_type,  // 'invoice' or 'expense'
        public int $reconcilable_id,
        public ?int $amount = null,  // In cents, null = full transaction amount
    ) {}

    /**
     * Create from form request data or array.
     * 
     * @param array $data Must contain: reconcilable_type, reconcilable_id, and optionally amount
     */
    public static function fromArray(array $data): self
    {
        return new self(
            reconcilable_type: $data['reconcilable_type'],
            reconcilable_id: (int) $data['reconcilable_id'],
            amount: isset($data['amount']) ? (int) ($data['amount'] * 100) : null,
        );
    }

    /**
     * Validate reconcilable type is supported.
     */
    public function isValidType(): bool
    {
        return in_array($this->reconcilable_type, ['invoice', 'expense']);
    }

    /**
     * Get the full model class name for the reconcilable type.
     */
    public function getReconcilableClass(): string
    {
        return match($this->reconcilable_type) {
            'invoice' => 'App\Models\Invoice',
            'expense' => 'App\Models\Expense',
            default => throw new \InvalidArgumentException("Invalid reconcilable type: {$this->reconcilable_type}"),
        };
    }
}
