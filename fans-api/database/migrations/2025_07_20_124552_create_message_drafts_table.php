<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('message_drafts')) {
            Schema::create('message_drafts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('subject')->nullable();
                $table->text('content');
                $table->json('recipients')->nullable(); // Store recipient data as JSON
                $table->json('media')->nullable(); // Store media info as JSON
                $table->json('delivery_settings')->nullable(); // Store delivery settings as JSON
                $table->string('draft_name')->nullable(); // Optional name for the draft
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                
                $table->index('user_id');
                $table->index(['user_id', 'created_at']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('message_drafts');
    }
};
