<?php

namespace App\Domain\Banking\Actions;

use App\Domain\Banking\Data\BankTransactionData;
use App\Domain\Banking\Repositories\BankTransactionRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * ACTION: ImportBankTransactionsAction
 *
 * Idempotently imports bank transactions into the database.
 *
 * IDEMPOTENCY:
 * - Uses external_id (unique per organization) to detect duplicates
 * - If transaction with external_id already exists, skip it
 * - Safe to run the same CSV multiple times without creating duplicates
 *
 * MULTI-TENANCY:
 * - Automatically scopes to current organization via repository
 * - Each org has independent external_id namespace
 *
 * Usage:
 *   $action = app(ImportBankTransactionsAction::class);
 *   $transactions = $action->handle([
 *       new BankTransactionData(...),
 *       new BankTransactionData(...),
 *   ]);
 *   // Returns Collection of NEWLY created BankTransactions
 */
class ImportBankTransactionsAction
{
    public function __construct(
        private BankTransactionRepository $transactionRepository,
    ) {}

    /**
     * Handle the import
     *
     * @param array<int, BankTransactionData> $transactions
     * @return Collection newly created BankTransactions
     */
    public function handle(array $transactions): Collection
    {
        $created = [];

        foreach ($transactions as $transactionData) {
            // Check if this transaction already exists (idempotency)
            if ($this->transactionRepository->existsByExternalId($transactionData->external_id)) {
                continue;
            }

            // Create new transaction via repository
            $bankTransaction = $this->transactionRepository->create([
                'date' => $transactionData->date,
                'description' => $transactionData->description,
                'amount' => $transactionData->amount,
                'currency' => $transactionData->currency,
                'external_id' => $transactionData->external_id,
                'reconciled' => false,
            ]);

            $created[] = $bankTransaction;
        }

        return collect($created);
    }
}
