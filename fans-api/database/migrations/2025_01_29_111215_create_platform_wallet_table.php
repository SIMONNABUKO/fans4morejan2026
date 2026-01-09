<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('platform_wallet', function (Blueprint $table) {
            $table->id();
            $table->decimal('balance', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('platform_wallet_history', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2);
            $table->string('transaction_type'); // CREDIT or DEBIT
            $table->string('description');
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
            // Add relationships to identify users involved
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('receiver_id')->nullable();
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('set null');
            // Add fields to identify transaction context
            $table->string('fee_type'); // subscription, tip, media_purchase, etc.
            $table->decimal('original_amount', 10, 2); // Original transaction amount before fee
            $table->decimal('fee_percentage', 5, 2); // Fee percentage applied
            $table->string('status')->default('completed');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('platform_wallet_history');
        Schema::dropIfExists('platform_wallet');
    }
}; 