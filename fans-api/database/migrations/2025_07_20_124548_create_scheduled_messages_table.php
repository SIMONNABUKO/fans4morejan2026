<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('scheduled_messages')) {
            Schema::create('scheduled_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sender_id');
                $table->string('subject')->nullable();
                $table->text('content');
                $table->json('recipients'); // Store recipient data as JSON
                $table->json('media')->nullable(); // Store media info as JSON
                $table->timestamp('scheduled_for');
                $table->string('timezone')->default('UTC');
                $table->enum('recurring_type', ['daily', 'weekly', 'monthly'])->nullable();
                $table->date('recurring_end_date')->nullable();
                $table->json('delivery_options')->nullable(); // Store delivery settings as JSON
                $table->enum('status', ['pending', 'sent', 'cancelled', 'failed'])->default('pending');
                $table->unsignedBigInteger('mass_message_id')->nullable(); // Link to mass message campaign
                $table->timestamp('sent_at')->nullable();
                $table->text('failure_reason')->nullable();
                $table->json('analytics')->nullable(); // Store analytics data as JSON
                $table->timestamps();

                $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
                
                $table->index('sender_id');
                $table->index('scheduled_for');
                $table->index('status');
                $table->index(['sender_id', 'status']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('scheduled_messages');
    }
};
