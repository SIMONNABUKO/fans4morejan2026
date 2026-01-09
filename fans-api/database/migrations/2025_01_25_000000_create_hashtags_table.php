<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hashtags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('posts_count')->default(0);
            $table->integer('followers_count')->default(0);
            $table->boolean('is_trending')->default(false);
            $table->timestamps();
            
            $table->index('name');
            $table->index('slug');
            $table->index('posts_count');
            $table->index('is_trending');
        });

        Schema::create('post_hashtags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('hashtag_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['post_id', 'hashtag_id']);
            $table->index(['hashtag_id', 'post_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('post_hashtags');
        Schema::dropIfExists('hashtags');
    }
}; 