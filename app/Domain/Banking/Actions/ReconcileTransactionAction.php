<?php

namespace App\Domain\Banking\Actions;

use App\Domain\Banking\Data\ReconciliationData;
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
 *   $data = ReconciliationData::fromArray($request->validated());
 *   $reconciliation = $action->handle($bankTransaction, $data);
 */
class ReconcileTransactionAction
{
    /**
     * Handle the reconciliation
     *
     * @param BankTransaction $transaction
     * @param ReconciliationData $data
     * @return Reconciliation
     * @throws \InvalidArgumentException
     */
    public function handle(BankTransaction $transaction, ReconciliationData $data): Reconciliation
    {
        // Validate data
        if (!$data->isValidType()) {
            throw new \InvalidArgumentException("Invalid reconcilable type: {$data->reconcilable_type}");
        }

        // Get the reconcilable model class
        $reconcilableClass = $data->getReconcilableClass();
        $reconcilable = $reconcilableClass::find($data->reconcilable_id);

        if (!$reconcilable) {
            throw new \InvalidArgumentException(
                "{$data->reconcilable_type} with ID {$data->reconcilable_id} not found"
            );
        }

        // Use full transaction amount if not specified
        $reconciliationAmount = $data->amount ?? abs($transaction->amount);

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
