<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('mass_messages')) {
            Schema::create('mass_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sender_id');
                $table->string('subject')->nullable();
                $table->text('content');
                $table->json('recipients_data'); // Store original recipient data
                $table->json('media')->nullable(); // Store media info as JSON
                $table->json('delivery_options')->nullable(); // Store delivery settings as JSON
                $table->unsignedInteger('total_recipients')->default(0);
                $table->unsignedInteger('sent_count')->default(0);
                $table->unsignedInteger('delivered_count')->default(0);
                $table->unsignedInteger('failed_count')->default(0);
                $table->unsignedInteger('opened_count')->default(0);
                $table->unsignedInteger('clicked_count')->default(0);
                $table->enum('status', ['draft', 'sending', 'sent', 'cancelled', 'failed'])->default('draft');
                $table->enum('type', ['immediate', 'scheduled', 'recurring'])->default('immediate');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->json('analytics')->nullable(); // Store detailed analytics as JSON
                $table->timestamps();

                $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
                
                $table->index('sender_id');
                $table->index('status');
                $table->index('type');
                $table->index(['sender_id', 'status']);
                $table->index('started_at');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('mass_messages');
    }
};
