<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tracking_links')) {
            Schema::create('tracking_links', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('creator_id');
                $table->string('name');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('tracking_links');
    }
}; 