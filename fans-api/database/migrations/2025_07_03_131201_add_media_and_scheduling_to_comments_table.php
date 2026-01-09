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
        Schema::table('comments', function (Blueprint $table) {
            $table->string('media_url')->nullable()->after('content');
            $table->dateTime('scheduled_for')->nullable()->after('media_url');
            $table->dateTime('delete_at')->nullable()->after('scheduled_for');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn(['media_url', 'scheduled_for', 'delete_at']);
        });
    }
};
