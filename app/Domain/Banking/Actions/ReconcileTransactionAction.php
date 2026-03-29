<?php

namespace App\Domain\Banking\Actions;

use App\Models\BankTransaction;
use App\Models\Reconciliation;
use Illuminate\Database\Eloquent\Model;

/**
 * ACTION: ReconcileTransactionAction
 *
 * Reconciles a bank transaction with an Invoice or Expense.
 *
 * POLYMORPHIC RECONCILIATION:
 * - Supports reconciling with Invoice OR Expense
 * - Uses Laravel's polymorphic relationships (morphTo)
 * - Prevents double-reconciliation via database constraints
 *
 * PARTIAL PAYMENTS:
 * - Amount parameter allows recording partial reconciliations
 * - Multiple reconciliations can point to the same Invoice (multi-part payment)
 *
 * Usage:
 *   $action = app(ReconcileTransactionAction::class);
 *   $reconciliation = $action->handle(
 *       transaction: $bankTransaction,
 *       reconcilable: $invoice,
 *       amount: 50000  // €500 in cents (or null to use full transaction amount)
 *   );
 */
class ReconcileTransactionAction
{
    /**
     * Handle the reconciliation
     *
     * @param BankTransaction $transaction
     * @param Model $reconcilable Invoice or Expense model
     * @param int|null $amount in cents (uses transaction amount if null)
     * @return Reconciliation
     * @throws \InvalidArgumentException
     */
    public function handle(
        BankTransaction $transaction,
        Model $reconcilable,
        ?int $amount = null,
    ): Reconciliation {
        // Validate reconcilable model
        $allowedModels = ['App\Models\Invoice', 'App\Models\Expense'];
        $reconcilableClass = get_class($reconcilable);

        if (!in_array($reconcilableClass, $allowedModels)) {
            throw new \InvalidArgumentException(
                "Reconcilable must be an Invoice or Expense, got {$reconcilableClass}"
            );
        }

        // Use full transaction amount if not specified
        $reconciliationAmount = $amount ?? abs($transaction->amount);

        // Validate amount
        if ($reconciliationAmount <= 0) {
            throw new \InvalidArgumentException('Reconciliation amount must be positive');
        }

        if ($reconciliationAmount > abs($transaction->amount)) {
            throw new \InvalidArgumentException(
                "Reconciliation amount ({$reconciliationAmount}) cannot exceed transaction amount ({$transaction->amount})"
            );
        }

        // Create reconciliation record
        $reconciliation = Reconciliation::create([
            'bank_transaction_id' => $transaction->id,
            'reconcilable_type' => $reconcilableClass,
            'reconcilable_id' => $reconcilable->id,
            'amount' => $reconciliationAmount,
        ]);

        // Mark transaction as reconciled if this is the full amount
        if ($reconciliationAmount === abs($transaction->amount)) {
            $transaction->update(['reconciled' => true]);
        }

        return $reconciliation;
    }
}
