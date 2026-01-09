<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('media_albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('media_album_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_album_id')->constrained()->onDelete('cascade');
            $table->foreignId('media_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['media_album_id', 'media_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('media_album_items');
        Schema::dropIfExists('media_albums');
    }
};

