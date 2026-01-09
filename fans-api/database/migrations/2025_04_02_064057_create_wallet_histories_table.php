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
        if (!Schema::hasTable('wallet_histories')) {
            Schema::create('wallet_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('wallet_id');
                $table->decimal('amount', 10, 2);
                $table->string('balance_type'); // total, pending, available
                $table->string('transaction_type'); // credit, debit, transfer
                $table->string('description');
                $table->string('status')->default('completed'); // pending, completed, failed
                $table->string('reference_id')->nullable();
                $table->string('transactionable_type')->nullable();
                $table->unsignedBigInteger('transactionable_id')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_histories');
    }
};