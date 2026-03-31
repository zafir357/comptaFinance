<?php

namespace App\Domain\Expenses\Actions;

use App\Domain\Expenses\Data\ExpenseData;
use App\Domain\Expenses\Events\ExpenseCreated;
use App\Domain\Expenses\Jobs\ProcessReceiptJob;
use App\Domain\Expenses\Repositories\ExpenseRepository;
use App\Models\Expense;

/**
 * ACTION: CreateExpenseAction
 *
 * Creates a new expense record.
 * Single-responsibility: handles expense creation workflow.
 *
 * IMPORTANT: This is where we ensure:
 * - Organization is set (automatic via repository)
 * - Receipt file path is stored
 * - Event is dispatched
 * - Receipt processing job is queued if needed
 *
 * Usage:
 *   $data = ExpenseData::fromArray($request->validated());
 *   $expense = app(CreateExpenseAction::class)->handle($data);
 */
class CreateExpenseAction
{
    public function __construct(
        private ExpenseRepository $expenseRepository,
    ) {}

    public function handle(ExpenseData $data): Expense
    {
        // Create expense via repository
        $expense = $this->expenseRepository->create([
            'category' => $data->category,
            'supplier' => $data->supplier,
            'amount' => $data->amount,
            'vat_amount' => $data->vat_amount,
            'date' => $data->date,
            'receipt_path' => $data->receipt_path,
            'receipt_status' => $data->receipt_status,
            'notes' => $data->notes,
        ]);

        // Dispatch domain event
        event(new ExpenseCreated($expense));

        // Queue receipt processing job if receipt was uploaded
        if ($data->receipt_path && $data->receipt_status === 'pending') {
            ProcessReceiptJob::dispatch($expense);
        }

        return $expense;
    }
}
