<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('tier_id');
            $table->string('status')->default('pending');
            $table->string('ccbill_subscription_id')->nullable();
            $table->float('amount')->nullable();
            $table->integer('duration')->default(1);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->timestamp('cancel_date')->nullable();
            $table->timestamp('renew_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
};
