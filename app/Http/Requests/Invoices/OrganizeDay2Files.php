<?php

namespace App\Http\Requests\Invoices;

class OrganizeDay2Files
{
    public static function organize(): void
    {
        $basePath = dirname(__DIR__, 3);
        
        $directories = [
            'app/Jobs',
            'app/Notifications',
            'app/Http/Requests/Expenses',
            'app/Http/Requests/Banking',
        ];
        
        foreach ($directories as $dir) {
            $fullPath = $basePath . DIRECTORY_SEPARATOR . $dir;
            if (!is_dir($fullPath)) {
                @mkdir($fullPath, 0755, true);
            }
        }
        
        // Move files
        $moves = [
            'ProcessReceiptJob.php' => 'app/Jobs/ProcessReceiptJob.php',
            'ReceiptProcessedNotification.php' => 'app/Notifications/ReceiptProcessedNotification.php',
            'app/Http/Requests/Invoices/StoreExpenseRequest.php' => 'app/Http/Requests/Expenses/StoreExpenseRequest.php',
            'app/Http/Requests/Invoices/ImportBankTransactionsRequest.php' => 'app/Http/Requests/Banking/ImportBankTransactionsRequest.php',
        ];
        
        foreach ($moves as $from => $to) {
            $fromPath = $basePath . DIRECTORY_SEPARATOR . $from;
            $toPath = $basePath . DIRECTORY_SEPARATOR . $to;
            
            if (file_exists($fromPath) && !file_exists($toPath)) {
                @rename($fromPath, $toPath);
            }
        }
        
        // Clean up temporary files
        $tempFiles = [
            'setup_files.bat',
            'organize_files.php',
            'create_dirs.sh',
            'DAY2_FILE_ORGANIZATION.md',
            'app/Http/Requests/ProcessReceiptJob_Jobs.php',
            'app/Http/Requests/ReceiptProcessedNotification_Notifications.php',
            'app/Http/Requests/Invoices/StoreExpenseRequest.php',
            'app/Http/Requests/Invoices/ImportBankTransactionsRequest.php',
        ];
        
        foreach ($tempFiles as $file) {
            $filePath = $basePath . DIRECTORY_SEPARATOR . $file;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    }
}
