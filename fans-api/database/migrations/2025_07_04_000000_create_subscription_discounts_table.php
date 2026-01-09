<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('subscription_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tier_id'); // references tiers table
            $table->string('label');
            $table->unsignedInteger('max_uses')->nullable();
            $table->decimal('discounted_price', 10, 2)->nullable();
            $table->unsignedTinyInteger('discount_percent')->nullable();
            $table->decimal('amount_off', 10, 2)->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->unsignedInteger('duration_days')->nullable();
            $table->boolean('exclude_previous_claimers')->default(false);
            $table->unsignedBigInteger('created_by'); // creator id
            $table->timestamps();

            $table->foreign('tier_id')->references('id')->on('tiers')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_discounts');
    }
}; 