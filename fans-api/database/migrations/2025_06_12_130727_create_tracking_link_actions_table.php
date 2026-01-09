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
        if (!Schema::hasTable('tracking_link_actions')) {
            Schema::create('tracking_link_actions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tracking_link_id');
                $table->string('action_type'); // e.g., 'signup', 'subscription', 'purchase'
                $table->unsignedBigInteger('user_id')->nullable();
                $table->json('action_data')->nullable(); // Additional data about the action
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_link_actions');
    }
};
