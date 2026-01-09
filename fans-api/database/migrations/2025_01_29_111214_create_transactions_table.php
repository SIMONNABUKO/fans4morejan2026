<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->decimal('amount', 10, 2);
            $table->string('type');
            $table->string('status');
            $table->unsignedBigInteger('tier_id')->nullable();
            $table->string('ccbill_transaction_id')->nullable();
            $table->string('ccbill_subscription_id')->nullable();
            $table->string('ccbill_payment_token')->nullable();
            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('tier_id')->references('id')->on('tiers')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};

