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
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'status')) {
                $table->string('status')->default('active')->after('content');
            }
            if (!Schema::hasColumn('messages', 'is_premium')) {
                $table->boolean('is_premium')->default(false)->after('status');
            }
            if (!Schema::hasColumn('messages', 'price')) {
                $table->decimal('price', 8, 2)->nullable()->after('is_premium');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'price')) {
                $table->dropColumn('price');
            }
            if (Schema::hasColumn('messages', 'is_premium')) {
                $table->dropColumn('is_premium');
            }
            if (Schema::hasColumn('messages', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
}; 