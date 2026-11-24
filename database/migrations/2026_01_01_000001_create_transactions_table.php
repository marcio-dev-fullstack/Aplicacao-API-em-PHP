<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payer_account_id')->nullable()->constrained('accounts')->onDelete('restrict');
            $table->foreignUuid('payee_account_id')->constrained('accounts')->onDelete('restrict');
            $table->bigInteger('amount');
            $table->enum('type', ['transfer', 'deposit', 'withdrawal']);
            $table->enum('status', ['pending', 'completed', 'failed']);
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->timestamps();

            $table->index('payer_account_id');
            $table->index('payee_account_id');
            $table->index('idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
