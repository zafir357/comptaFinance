<?php

use App\Http\Controllers\Api\InvoiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    /**
     * Invoice API Routes
     */
    Route::apiResource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid']);
});
