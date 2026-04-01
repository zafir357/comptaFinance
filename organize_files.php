<?php

// This script organizes files created during Day 2 implementation

$basePath = __DIR__;

// Create directories
$directories = [
    'app/Jobs',
    'app/Notifications',
    'app/Http/Requests/Expenses',
    'app/Http/Requests/Banking',
];

foreach ($directories as $dir) {
    $fullPath = $basePath . DIRECTORY_SEPARATOR . $dir;
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0755, true);
        echo "Created directory: $dir\n";
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
    
    if (file_exists($fromPath)) {
        if (!rename($fromPath, $toPath)) {
            echo "Error moving: $from to $to\n";
        } else {
            echo "Moved: $from => $to\n";
        }
    }
}

// Clean up temporary files
$temp_files = [
    'setup_files.bat',
    'app/Http/Requests/ProcessReceiptJob_Jobs.php',
    'app/Http/Requests/ReceiptProcessedNotification_Notifications.php',
];

foreach ($temp_files as $file) {
    $filePath = $basePath . DIRECTORY_SEPARATOR . $file;
    if (file_exists($filePath)) {
        if (!unlink($filePath)) {
            echo "Error deleting: $file\n";
        } else {
            echo "Deleted: $file\n";
        }
    }
}

echo "\nFile organization complete!\n";
