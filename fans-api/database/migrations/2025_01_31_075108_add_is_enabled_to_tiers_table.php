<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tiers', function (Blueprint $table) {
            $table->boolean('is_enabled')->default(true)->after('max_subscribers_enabled');
        });
    }

    public function down()
    {
        Schema::table('tiers', function (Blueprint $table) {
            $table->dropColumn('is_enabled');
        });
    }
};