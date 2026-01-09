<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bio_color')->nullable()->after('bio');
            $table->string('bio_font')->nullable()->after('bio_color');
            $table->string('location')->nullable()->after('media_watermark');
            $table->dropColumn(['country_name', 'region_name', 'city_name']);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bio_color', 'bio_font', 'location']);
            $table->string('country_name')->nullable();
            $table->string('region_name')->nullable();
            $table->string('city_name')->nullable();
        });
    }
}; 