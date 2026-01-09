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
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'scheduled_for')) {
                $table->dateTime('scheduled_for')->nullable()->after('moderation_note');
            }
            if (!Schema::hasColumn('posts', 'expires_at')) {
                $table->dateTime('expires_at')->nullable()->after('scheduled_for');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'scheduled_for')) {
                $table->dropColumn('scheduled_for');
            }
            if (Schema::hasColumn('posts', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });
    }
};
