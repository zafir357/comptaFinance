<?php

namespace App\Domain\Expenses\Jobs;

use App\Domain\Expenses\Events\ExpenseReceiptProcessed;
use App\Models\Expense;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * JOB: ProcessReceiptJob
 *
 * Queue job for processing expense receipts.
 * In production, this would:
 * - Extract text via OCR (Tesseract, Google Vision, etc.)
 * - Parse line items and amounts
 * - Verify totals
 * - Store metadata
 *
 * For now, simulates with a delay to demonstrate async processing.
 *
 * Usage:
 *   ProcessReceiptJob::dispatch($expense);
 *   // Or queue it directly from CreateExpenseAction
 */
class ProcessReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Delete the job if its models no longer exist.
     */
    public bool $deleteWhenMissing = true;

    public function __construct(
        public Expense $expense,
    ) {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // In production, you would do actual OCR here
        // For now, we simulate processing with a small delay

        try {
            // Simulate OCR/extraction delay
            sleep(1);

            // Simulate OCR result (in reality would parse receipt image)
            $result = $this->simulateOcrExtraction();

            // Update expense with processed status
            $this->expense->update([
                'receipt_status' => 'processed',
                'receipt_processed_at' => now(),
            ]);

            // Dispatch success event
            event(new ExpenseReceiptProcessed(
                $this->expense,
                success: true,
                result: $result,
            ));
        } catch (\Throwable $e) {
            // Mark as failed
            $this->expense->update([
                'receipt_status' => 'failed',
                'receipt_processed_at' => now(),
            ]);

            // Dispatch failure event
            event(new ExpenseReceiptProcessed(
                $this->expense,
                success: false,
                result: ['error' => $e->getMessage()],
            ));

            // Re-throw to retry job
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Mark expense as failed after all retries exhausted
        $this->expense->update([
            'receipt_status' => 'failed',
            'receipt_processed_at' => now(),
        ]);

        // Log the error
        \Log::error('Expense receipt processing failed', [
            'expense_id' => $this->expense->id,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Simulate OCR extraction from receipt image.
     *
     * In production, this would use a real OCR service.
     * Example result structure:
     * [
     *     'supplier' => 'Extracted Supplier Name',
     *     'total' => 99.99,
     *     'date' => '2026-03-29',
     *     'items' => [
     *         ['description' => '...', 'price' => ...],
     *     ],
     * ]
     */
    private function simulateOcrExtraction(): array
    {
        return [
            'supplier' => $this->expense->supplier,
            'date' => $this->expense->date->format('Y-m-d'),
            'total' => ($this->expense->amount + $this->expense->vat_amount) / 100,
            'currency' => 'EUR',
            'confidence' => 0.95,
            'extraction_method' => 'ocr_simulated',
        ];
    }
}
