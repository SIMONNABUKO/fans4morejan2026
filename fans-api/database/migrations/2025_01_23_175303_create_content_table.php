<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('content')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->text('content')->nullable();
            $table->timestamps();
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->morphs('mediable');
            $table->string('type');
            $table->string('url');
            $table->timestamps();
        });

        Schema::create('media_previews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained()->onDelete('cascade');
            $table->string('url');
            $table->timestamps();
        });

        Schema::create('permission_sets', function (Blueprint $table) {
            $table->id();
            $table->morphs('permissionable');
            $table->decimal('price', 8, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_set_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->string('value')->nullable();
            $table->timestamps();
        });

       
    }

    public function down()
    {
        Schema::dropIfExists('post_purchases');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('permission_sets');
        Schema::dropIfExists('media_previews');
        Schema::dropIfExists('media');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('posts');
    }
};

