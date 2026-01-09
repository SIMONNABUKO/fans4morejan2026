<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('subscriptions', 'transaction_id')) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('transaction_id')->nullable()->after('tier_id')->constrained('transactions')->onDelete('set null');
        });
    }

    public function down()
    {
        if (!Schema::hasColumn('subscriptions', 'transaction_id')) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
            $table->dropColumn('transaction_id');
        });
    }
}; 
