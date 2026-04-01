<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_transaction_invoice', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            // Applied amount in cents (negative values for debits)
            $table->bigInteger('applied_amount');
            $table->timestamps();

            $table->index(['bank_transaction_id', 'invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transaction_invoice');
    }
};
