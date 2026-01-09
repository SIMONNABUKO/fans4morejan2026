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
        Schema::create('payout_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // bank, card, paypal, etc.
            $table->string('provider'); // COSMO, Chase, PayPal, etc.
            $table->string('account_number');
            $table->string('account_name')->nullable();
            $table->boolean('is_default')->default(false);
            $table->json('details')->nullable(); // Additional details specific to the method
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payout_methods');
    }
};