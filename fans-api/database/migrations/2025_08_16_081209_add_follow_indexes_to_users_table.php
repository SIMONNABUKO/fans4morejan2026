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
        Schema::table('users', function (Blueprint $table) {
            // Add indexes for follow-related queries
            $table->index(['role', 'can_be_followed'], 'users_role_followable_index');
            $table->index(['id', 'role'], 'users_id_role_index');
        });

        // Add indexes to the followers pivot table if it exists
        if (Schema::hasTable('followers')) {
            Schema::table('followers', function (Blueprint $table) {
                // Composite index for follow/unfollow operations
                $table->index(['follower_id', 'followed_id'], 'followers_follower_followed_index');
                $table->index(['followed_id', 'follower_id'], 'followers_followed_follower_index');
                $table->index('created_at', 'followers_created_at_index');
            });
        }

        // Add indexes to the jobs table for better queue performance
        Schema::table('jobs', function (Blueprint $table) {
            // Composite index for job processing
            $table->index(['queue', 'reserved_at'], 'jobs_queue_reserved_index');
            $table->index(['queue', 'available_at'], 'jobs_queue_available_index');
            $table->index(['queue', 'reserved_at', 'available_at'], 'jobs_queue_processing_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_followable_index');
            $table->dropIndex('users_id_role_index');
        });

        if (Schema::hasTable('followers')) {
            Schema::table('followers', function (Blueprint $table) {
                $table->dropIndex('followers_follower_followed_index');
                $table->dropIndex('followers_followed_follower_index');
                $table->dropIndex('followers_created_at_index');
            });
        }

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropIndex('jobs_queue_reserved_index');
            $table->dropIndex('jobs_queue_available_index');
            $table->dropIndex('jobs_queue_processing_index');
        });
    }
};
