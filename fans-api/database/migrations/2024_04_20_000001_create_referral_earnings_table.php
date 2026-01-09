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
        if (!Schema::hasTable('referral_earnings')) {
            Schema::create('referral_earnings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('referral_id')->constrained('referrals')->onDelete('cascade');
                $table->decimal('amount', 10, 2);
                $table->enum('type', ['user', 'creator']);
                $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
                $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_earnings');
    }
}; 