<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('automated_messages')) {
            Schema::create('automated_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('trigger'); // new_follower, new_subscriber, etc.
                $table->text('content');
                $table->json('media')->nullable(); // Store media info as JSON
                $table->integer('sent_delay')->default(0); // Delay in seconds
                $table->integer('cooldown')->default(0); // Cooldown in seconds
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('automated_messages');
    }
};