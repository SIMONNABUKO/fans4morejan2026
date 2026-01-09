<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stats', function (Blueprint $table) {
            $table->id();
            $table->morphs('statable');
            $table->unsignedInteger('total_likes')->default(0);
            $table->unsignedInteger('total_views')->default(0);
            $table->unsignedInteger('total_bookmarks')->default(0);
            $table->unsignedInteger('total_comments')->default(0);
            $table->unsignedInteger('total_tips')->default(0);
            $table->decimal('total_tip_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stats');
    }
};

