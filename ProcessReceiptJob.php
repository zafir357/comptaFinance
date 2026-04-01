<?php

namespace App\Jobs;

use App\Models\Expense;
use App\Notifications\ReceiptProcessedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 30;

    public function __construct(
        public Expense $expense
    ) {
    }

    public function handle(): void
    {
        if (!$this->expense->receipt_path || !Storage::disk('receipts')->exists($this->expense->receipt_path)) {
            $this->expense->update(['receipt_status' => 'failed']);
            \Log::warning('Receipt file not found', ['expense_id' => $this->expense->id]);
            return;
        }

        try {
            $filePath = $this->expense->receipt_path;
            $fullPath = Storage::disk('receipts')->path($filePath);
            $fileSize = Storage::disk('receipts')->size($filePath);
            $mimeType = Storage::disk('receipts')->mimeType($filePath);

            $metadata = [
                'file_size' => $fileSize,
                'file_size_human' => $this->formatBytes($fileSize),
                'mime_type' => $mimeType,
                'processed_at' => now()->toDateTimeString(),
                'original_filename' => basename($filePath),
            ];

            if (str_starts_with($mimeType, 'image/')) {
                $imageInfo = getimagesize($fullPath);
                if ($imageInfo) {
                    $metadata['width'] = $imageInfo[0];
                    $metadata['height'] = $imageInfo[1];
                    $metadata['format'] = image_type_to_extension($imageInfo[2], false);
                }
            }

            $metadata['ocr_attempted'] = true;
            $metadata['ocr_text'] = $this->simulateOcr($fullPath, $mimeType);

            $this->expense->update([
                'receipt_status' => 'processed',
                'receipt_metadata' => json_encode($metadata),
            ]);

            $this->expense->organization->users->each(function ($user) {
                $user->notify(new ReceiptProcessedNotification($this->expense));
            });

            \Log::info('Receipt processed successfully', [
                'expense_id' => $this->expense->id,
                'file_size' => $metadata['file_size_human'],
            ]);

        } catch (\Exception $e) {
            \Log::error('Receipt processing failed', [
                'expense_id' => $this->expense->id,
                'receipt_path' => $this->expense->receipt_path,
                'error' => $e->getMessage(),
            ]);

            $this->expense->update(['receipt_status' => 'failed']);
            throw $e;
        }
    }

    private function simulateOcr(string $filePath, string $mimeType): string
    {
        return "OCR processing - File: " . basename($filePath);
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function failed(\Throwable $exception): void
    {
        \Log::critical('Receipt processing permanently failed', [
            'expense_id' => $this->expense->id,
            'error' => $exception->getMessage(),
        ]);

        $this->expense->update([
            'receipt_status' => 'failed',
            'receipt_metadata' => json_encode([
                'error' => $exception->getMessage(),
                'failed_at' => now()->toDateTimeString(),
            ]),
        ]);
    }
}
