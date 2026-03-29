<?php

/**
 * EXPENSE MODULE - BACKEND IMPLEMENTATION
 *
 * Complete backend for expense management in ComptaFinance.
 * Includes DTOs, Actions, Repositories, Jobs, and Domain Events.
 *
 * FILES CREATED:
 * ============================================================================
 *
 * 1. app/Domain/Expenses/Data/ExpenseData.php
 *    - DTO (Data Transfer Object) for type-safe expense data
 *    - Converts amounts from euros to cents (integer storage)
 *    - Factory method: fromArray() for request data
 *    - Validation methods: validCategories()
 *    - Helper methods: total(), totalInEuros()
 *
 * 2. app/Domain/Expenses/Actions/CreateExpenseAction.php
 *    - Action class handling expense creation workflow
 *    - Uses dependency injection for services
 *    - Creates Expense model with automatic organization scoping
 *    - Dispatches ExpenseCreated domain event
 *    - Queues ProcessReceiptJob if receipt uploaded
 *
 * 3. app/Domain/Expenses/Repositories/ExpenseRepository.php
 *    - Data access abstraction extending BaseRepository
 *    - All queries automatically scoped to current organization
 *    - Methods:
 *      * all(), paginate(), find(), findOrFail()
 *      * create(), update(), delete()
 *      * byCategory(), byReceiptStatus()
 *      * pendingReceipts(), failedReceipts()
 *      * between(), forSupplier()
 *      * recent(), totalByCategory(), totalBetween()
 *
 * 4. app/Domain/Expenses/Events/ExpenseCreated.php
 *    - Domain event fired when expense is created
 *    - Supports broadcasting to organization channels
 *    - Includes expense details in broadcast data
 *    - Listeners can react to: notifications, audit logs, side effects
 *
 * 5. app/Domain/Expenses/Events/ExpenseReceiptProcessed.php
 *    - Domain event fired when receipt processing completes
 *    - Indicates success/failure and processing result
 *    - Supports broadcasting to organization channels
 *    - Listeners can update metadata, notify users, log results
 *
 * 6. app/Domain/Expenses/Jobs/ProcessReceiptJob.php
 *    - Queue job for asynchronous receipt processing
 *    - Implements ShouldQueue for background execution
 *    - Configurable: timeout=120s, tries=3, deleteWhenMissing=true
 *    - Simulates OCR extraction with small delay
 *    - Handles failures gracefully with event dispatch
 *    - Includes simulateOcrExtraction() for demo OCR result
 *
 * 7. database/migrations/2026_03_29_000000_add_notes_to_expenses_table.php
 *    - Adds 'notes' column to expenses table
 *    - Supports expense notes/descriptions
 *
 * ============================================================================
 * ARCHITECTURE OVERVIEW
 * ============================================================================
 *
 * MULTI-TENANCY SUPPORT:
 *   - Organization automatically set via trait (BelongsToOrganization)
 *   - BaseRepository auto-scopes all queries to current organization
 *   - CurrentOrganization service provides org ID from session
 *
 * MONEY HANDLING:
 *   - All amounts stored in CENTS (integer) in database
 *   - DTO converts euros to cents on input
 *   - Model has accessors to convert back to euros for display
 *
 * ASYNC PROCESSING:
 *   - CreateExpenseAction queues ProcessReceiptJob if receipt uploaded
 *   - Job runs in background with configurable retries
 *   - Dispatches ExpenseReceiptProcessed event on completion
 *   - Failures logged and marked in database
 *
 * EVENT-DRIVEN:
 *   - ExpenseCreated fired on expense creation
 *   - ExpenseReceiptProcessed fired on receipt completion
 *   - Broadcast to organization-specific channels
 *   - Enables real-time UI updates, notifications, etc.
 *
 * QUERY PATTERNS:
 *   $repo = app(ExpenseRepository::class);
 *
 *   // Basic queries
 *   $repo->all();                                  // All expenses
 *   $repo->paginate(15);                          // Paginated
 *   $repo->find($id);                             // By ID
 *
 *   // Filtering
 *   $repo->byCategory('travel')->get();           // By category
 *   $repo->byReceiptStatus('pending')->get();     // Pending receipts
 *   $repo->forSupplier('Acme Corp')->get();       // By supplier
 *
 *   // Date ranges
 *   $repo->between($start, $end)->get();          // Date range
 *   $repo->recent(10)->get();                     // Last 10
 *
 *   // Analytics
 *   $totals = $repo->totalByCategory();           // By category
 *   $total = $repo->totalBetween($start, $end);   // Total for period
 *
 * CATEGORY OPTIONS:
 *   - travel (déplacement)
 *   - meals (repas)
 *   - supplies (fournitures)
 *   - utilities (services)
 *   - maintenance (entretien)
 *   - software (logiciels)
 *   - other (autre)
 *
 * ============================================================================
 * USAGE EXAMPLES
 * ============================================================================
 *
 * 1. CREATE EXPENSE:
 *    $data = ExpenseData::fromArray($request->validated());
 *    $expense = app(CreateExpenseAction::class)->handle($data);
 *
 * 2. FETCH EXPENSES:
 *    $repo = app(ExpenseRepository::class);
 *    $expenses = $repo->paginate(15);
 *
 * 3. FILTER BY CATEGORY:
 *    $travels = $repo->byCategory('travel')->get();
 *
 * 4. GET PENDING RECEIPTS:
 *    $pending = $repo->pendingReceipts()->get();
 *
 * 5. PROCESS RECEIPT (manual):
 *    ProcessReceiptJob::dispatch($expense);
 *
 * ============================================================================
 * TYPE HINTS & VALIDATION
 * ============================================================================
 *
 * ExpenseData properties:
 *   - category: string
 *   - supplier: string
 *   - amount: int (cents)
 *   - vat_amount: int (cents)
 *   - date: Carbon
 *   - receipt_path: nullable string
 *   - receipt_status: nullable string ('pending', 'processing', 'processed', 'failed')
 *   - notes: nullable string
 *
 * Database fields:
 *   - id: bigint
 *   - organization_id: bigint (FK)
 *   - category: string
 *   - supplier: string
 *   - date: date
 *   - amount: integer (cents)
 *   - vat_amount: integer (cents)
 *   - receipt_path: string nullable
 *   - receipt_status: enum
 *   - receipt_processed_at: datetime nullable
 *   - notes: text nullable
 *   - created_at: datetime
 *   - updated_at: datetime
 *
 * ============================================================================
 * INTEGRATION POINTS
 * ============================================================================
 *
 * Next steps to complete the expense module:
 *
 *   1. FormRequest Validation:
 *      - Create app/Http/Requests/Expenses/StoreExpenseRequest.php
 *      - Create app/Http/Requests/Expenses/UpdateExpenseRequest.php
 *
 *   2. API Controller:
 *      - Create app/Http/Controllers/Api/ExpenseController.php
 *      - Implement index, store, show, update, destroy
 *
 *   3. Policy:
 *      - Create app/Policies/ExpensePolicy.php
 *      - Authorize CRUD operations
 *
 *   4. Livewire Components:
 *      - Create app/Livewire/Expenses/ExpenseList.php
 *      - Create app/Livewire/Expenses/ExpenseEditor.php
 *      - Create app/Livewire/Expenses/ReceiptUploader.php
 *
 *   5. Blade Views:
 *      - Create resources/views/expenses/index.blade.php
 *      - Create resources/views/expenses/show.blade.php
 *      - Create resources/views/expenses/create.blade.php
 *
 *   6. Routes:
 *      - Add routes/web.php entries for expenses
 *      - Add routes/api.php entries for API
 *
 * ============================================================================
 * TESTING GUIDELINES
 * ============================================================================
 *
 * Unit Tests:
 *   - ExpenseDataTest: Factory methods, calculations
 *   - CreateExpenseActionTest: Creation, event dispatch
 *
 * Feature Tests:
 *   - CreateExpenseTest: End-to-end creation
 *   - ExpenseAuthorizationTest: Policy enforcement
 *   - ReceiptProcessingTest: Job execution
 *   - ExpenseRepositoryTest: Query methods
 *
 * ============================================================================
 */
