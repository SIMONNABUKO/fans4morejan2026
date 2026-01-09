<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bookmark_albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('bookmarks', function (Blueprint $table) {
            $table->foreignId('bookmark_album_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('bookmarks', function (Blueprint $table) {
            $table->dropForeign(['bookmark_album_id']);
            $table->dropColumn('bookmark_album_id');
        });

        Schema::dropIfExists('bookmark_albums');
    }
};

