<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sender_account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignUuid('receiver_account_id')->constrained('accounts')->onDelete('cascade');
            $table->string('receiver_account_number');
            $table->string('receiver_account_holder_name');
            $table->foreignUuid('currency_id')->constrained('currencies')->onDelete('restrict');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'completed', 'failed']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
