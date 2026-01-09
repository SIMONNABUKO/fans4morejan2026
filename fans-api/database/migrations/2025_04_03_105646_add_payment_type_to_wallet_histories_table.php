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
        Schema::table('wallet_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('wallet_histories', 'payment_type')) {
                $table->string('payment_type')->nullable()->after('transaction_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_histories', function (Blueprint $table) {
            if (Schema::hasColumn('wallet_histories', 'payment_type')) {
                $table->dropColumn('payment_type');
            }
        });
    }
};
