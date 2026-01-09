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
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'purchasable_type')) {
                $table->string('purchasable_type')->nullable()->after('additional_data');
            }
            if (!Schema::hasColumn('transactions', 'purchasable_id')) {
                $table->unsignedBigInteger('purchasable_id')->nullable()->after('purchasable_type');
            }
        });
        
        // Add index for better query performance (only if it doesn't exist)
        if (!Schema::hasIndex('transactions', 'transactions_purchasable_type_purchasable_id_index')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index(['purchasable_type', 'purchasable_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'purchasable_id')) {
                $table->dropColumn('purchasable_id');
            }
            if (Schema::hasColumn('transactions', 'purchasable_type')) {
                $table->dropColumn('purchasable_type');
            }
            $table->dropIndex(['purchasable_type', 'purchasable_id']);
        });
    }
}; 