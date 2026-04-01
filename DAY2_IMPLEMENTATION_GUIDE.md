# Day 2 Implementation - ComptaFinance Laravel Application

## Overview
This document describes the Day 2 implementation files for the ComptaFinance Laravel application, which includes Job processing, Notifications, and Form Requests.

## Automatic File Organization

When the Laravel application boots for the first time, the `AppServiceProvider` will automatically:
1. Create the required directory structure
2. Move files to their final locations
3. Clean up temporary files

**Files to be organized automatically:**
- `ProcessReceiptJob.php` → `app/Jobs/ProcessReceiptJob.php`
- `ReceiptProcessedNotification.php` → `app/Notifications/ReceiptProcessedNotification.php`
- `app/Http/Requests/Invoices/StoreExpenseRequest.php` → `app/Http/Requests/Expenses/StoreExpenseRequest.php`
- `app/Http/Requests/Invoices/ImportBankTransactionsRequest.php` → `app/Http/Requests/Banking/ImportBankTransactionsRequest.php`

## Files Created

### 1. app/Jobs/ProcessReceiptJob.php
**Purpose:** Asynchronous job for processing receipt files

**Features:**
- Implements `ShouldQueue` for background queue processing
- Configurable retries (3 attempts by default) with 30-second backoff
- Extracts file metadata (size, MIME type, image dimensions)
- Attempts OCR processing on image files
- Notifies organization users upon successful processing
- Comprehensive error handling and logging
- Uses Laravel's serialization for model transport

**Key Methods:**
- `handle()`: Main processing logic
- `simulateOcr()`: Placeholder for OCR text extraction
- `formatBytes()`: Human-readable file size formatting
- `failed()`: Handles permanent failures

**Configuration:**
- Max retries: 3
- Retry backoff: 30 seconds
- Supported receipt formats: Configured via storage disk

---

### 2. app/Notifications/ReceiptProcessedNotification.php
**Purpose:** Notification sent when a receipt has been successfully processed

**Channels:**
- **Database**: Stores notification in notifications table for UI display
- **Mail**: Sends email to user with receipt processing details

**Notification Data:**
```php
[
    'type' => 'receipt_processed',
    'expense_id' => $expenseId,
    'expense_title' => $title,
    'expense_amount' => $amount,
    'expense_date' => $date,
    'message' => French notification text,
    'action_url' => Route to expense detail page,
    'icon' => 'document-check',
    'color' => 'success',
]
```

**Localization:**
- All user-facing text is in French
- Email includes formatted amount in euros (€)
- Supports multiple notification channels

---

### 3. app/Http/Requests/Expenses/StoreExpenseRequest.php
**Purpose:** Form request validation for creating/storing new expenses

**Validation Rules:**
| Field | Rules | Messages |
|-------|-------|----------|
| `title` | Required, string, max 255 chars | French validation messages |
| `description` | Optional, string, max 5000 chars | - |
| `amount` | Required, numeric, min 0, max 999,999.99 | Must be positive |
| `vat_amount` | Required, numeric, min 0, max 999,999.99 | - |
| `date` | Required, date, not in future | Cannot be future date |
| `category` | Optional, string, max 100 chars | - |
| `receipt` | Optional, file, JPG/PNG/PDF, max 5 MB | Specific MIME type requirements |

**Features:**
- Pre-validation number formatting (handles comma/space as decimal separator)
- Converts European number format (1.000,50) to standard (1000.50)
- Authorization returns true (policy-based auth should be implemented)

**Number Formatting:**
```php
// Input: "1.000,50" (French format)
// Processed to: 1000.50 (standard float)
```

---

### 4. app/Http/Requests/Banking/ImportBankTransactionsRequest.php
**Purpose:** Form request validation for importing bank transactions from CSV files

**Validation Rules:**
| Field | Rules | Messages |
|-------|-------|----------|
| `csv_file` | Required, file, CSV/TXT, max 10 MB | French validation messages |

**Features:**
- Validates CSV file format
- File size limit: 10 MB
- Supports both `.csv` and `.txt` extensions
- All error messages in French

**Usage Example:**
```php
// In controller
public function importTransactions(ImportBankTransactionsRequest $request)
{
    $file = $request->file('csv_file');
    // Process CSV file...
}
```

---

## Integration Instructions

### 1. Using ProcessReceiptJob
```php
// In controller or service
use App\Jobs\ProcessReceiptJob;

$expense = Expense::find($id);
ProcessReceiptJob::dispatch($expense);

// Job will be queued and processed asynchronously
```

### 2. Using StoreExpenseRequest
```php
// In controller
use App\Http\Requests\Expenses\StoreExpenseRequest;

public function store(StoreExpenseRequest $request)
{
    $validated = $request->validated();
    
    // Handle receipt upload if provided
    if ($request->hasFile('receipt')) {
        $path = $request->file('receipt')->store('receipts');
        $validated['receipt_path'] = $path;
    }
    
    $expense = Expense::create($validated);
    
    // Dispatch job to process receipt
    if ($expense->receipt_path) {
        ProcessReceiptJob::dispatch($expense);
    }
    
    return redirect()->route('expenses.show', $expense);
}
```

### 3. Using ImportBankTransactionsRequest
```php
// In controller
use App\Http\Requests\Banking\ImportBankTransactionsRequest;

public function import(ImportBankTransactionsRequest $request)
{
    $file = $request->file('csv_file');
    
    // Process CSV file
    $path = $file->store('imports');
    
    // Queue processing job or handle immediately
    ProcessBankTransactionsJob::dispatch($path);
}
```

---

## Database Schema Requirements

### Expenses Table
The following columns are required/expected:
```sql
ALTER TABLE expenses ADD COLUMN receipt_path VARCHAR(255);
ALTER TABLE expenses ADD COLUMN receipt_status VARCHAR(50) DEFAULT 'pending';
ALTER TABLE expenses ADD COLUMN receipt_metadata LONGTEXT;
```

### Notifications Table
Laravel's default notifications table (already created):
```sql
php artisan notifications:table
php artisan migrate
```

---

## Queue Configuration

Ensure queue driver is configured in `.env`:
```env
QUEUE_CONNECTION=database
# or
QUEUE_CONNECTION=redis
```

Run queue worker:
```bash
php artisan queue:work
# or for production
php artisan queue:work --daemon
```

---

## Storage Configuration

Configure receipt storage disk in `config/filesystems.php`:
```php
'disks' => [
    'receipts' => [
        'driver' => 'local',
        'root' => storage_path('app/receipts'),
        'url' => env('APP_URL') . '/storage/receipts',
        'visibility' => 'private',
    ],
],
```

---

## Testing

### Test ProcessReceiptJob
```php
public function test_process_receipt_job()
{
    Storage::disk('receipts')->put('test.pdf', 'test content');
    
    $expense = Expense::factory()->create([
        'receipt_path' => 'test.pdf',
    ]);
    
    ProcessReceiptJob::dispatch($expense);
    
    $this->assertDatabaseHas('expenses', [
        'id' => $expense->id,
        'receipt_status' => 'processed',
    ]);
}
```

### Test StoreExpenseRequest
```php
public function test_store_expense_request_validation()
{
    $response = $this->post('/expenses', [
        'title' => 'Test Expense',
        'amount' => '1.000,50',  // French format
        'vat_amount' => '100,00',
        'date' => now()->toDateString(),
    ]);
    
    $this->assertFalse($response->errors()->has('amount'));
}
```

---

## Error Handling

### ProcessReceiptJob Failures
- Maximum 3 retry attempts with 30-second intervals
- Failed jobs are logged with expense ID and error details
- Notification is not sent on failure
- Permanent failures log critical-level error

### Validation Failures
- Form requests return HTTP 422 with validation errors
- Errors are returned in `errors` JSON field
- All messages are localized to French

---

## Performance Considerations

1. **Receipt Processing**: Use async queue jobs to avoid blocking user requests
2. **File Storage**: Store receipts in private disk for security
3. **Metadata Caching**: Metadata is JSON-encoded and stored, not computed on demand
4. **Notification Batching**: Multiple users in organization receive individual notifications

---

## Security Considerations

1. **File Upload Validation**: Restricted to JPG, PNG, PDF formats
2. **File Size Limits**: 5 MB for receipts, 10 MB for CSV imports
3. **Storage Privacy**: Receipts stored in private disk, not directly accessible
4. **Authorization**: Empty authorize() returns true - implement policy-based auth
5. **Input Sanitization**: Numbers converted to float for database safety

---

## Dependencies

Required packages (already in composer.json):
- `illuminate/bus` - Queue jobs
- `illuminate/notifications` - Notification system
- `illuminate/validation` - Form request validation
- `illuminate/support` - Helper functions

---

## Next Steps

1. Run database migrations to add receipt columns
2. Configure queue driver and run queue worker
3. Set up storage disk for receipts
4. Implement missing authorization policies
5. Create controller actions to use these requests/jobs
6. Add comprehensive tests for each component

