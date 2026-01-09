<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('list_type_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('list_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'list_type_id']);
            $table->unique(['user_id', 'list_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_lists');
    }
};