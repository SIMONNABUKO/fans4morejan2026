<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('message_templates')) {
            Schema::create('message_templates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('name'); // Template name
                $table->string('subject')->nullable();
                $table->text('content');
                $table->json('media')->nullable(); // Store media info as JSON
                $table->boolean('is_global')->default(false); // Whether it's available to all users
                $table->unsignedInteger('usage_count')->default(0); // Track how many times it's been used
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                
                $table->index('user_id');
                $table->index(['user_id', 'name']);
                $table->index('is_global');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('message_templates');
    }
};
