# Day 2 Files - Organization Instructions

Due to directory creation constraints, the following files have been created in the project root:
- ProcessReceiptJob.php
- ReceiptProcessedNotification.php
- app/Http/Requests/Invoices/StoreExpenseRequest.php
- app/Http/Requests/Invoices/ImportBankTransactionsRequest.php

They should be moved to these final locations:
- app/Jobs/ProcessReceiptJob.php
- app/Notifications/ReceiptProcessedNotification.php
- app/Http/Requests/Expenses/StoreExpenseRequest.php
- app/Http/Requests/Banking/ImportBankTransactionsRequest.php

On Linux/Mac, run:
```bash
mkdir -p app/Jobs app/Notifications app/Http/Requests/{Expenses,Banking}
mv ProcessReceiptJob.php app/Jobs/
mv ReceiptProcessedNotification.php app/Notifications/
mv app/Http/Requests/Invoices/StoreExpenseRequest.php app/Http/Requests/Expenses/
mv app/Http/Requests/Invoices/ImportBankTransactionsRequest.php app/Http/Requests/Banking/
rm setup_files.bat organize_files.php
```

On Windows, use Git Bash or PowerShell to execute the above commands.
