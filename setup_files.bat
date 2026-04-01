@echo off
setlocal enabledelayedexpansion

REM Create directories
if not exist "app\Jobs" mkdir app\Jobs
if not exist "app\Notifications" mkdir app\Notifications
if not exist "app\Http\Requests\Expenses" mkdir app\Http\Requests\Expenses
if not exist "app\Http\Requests\Banking" mkdir app\Http\Requests\Banking

REM Move files to correct locations
move ProcessReceiptJob.php app\Jobs\ProcessReceiptJob.php
move ReceiptProcessedNotification.php app\Notifications\ReceiptProcessedNotification.php
move StoreExpenseRequest.php app\Http\Requests\Expenses\StoreExpenseRequest.php
move ImportBankTransactionsRequest.php app\Http\Requests\Banking\ImportBankTransactionsRequest.php

echo Files created successfully!
