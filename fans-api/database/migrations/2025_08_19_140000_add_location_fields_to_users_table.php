<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('country_code', 2)->nullable()->after('location');
            $table->string('country_name')->nullable()->after('country_code');
            $table->string('region_name')->nullable()->after('country_name');
            $table->string('city_name')->nullable()->after('region_name');
            $table->decimal('latitude', 10, 8)->nullable()->after('city_name');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->timestamp('location_updated_at')->nullable()->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'country_code',
                'country_name', 
                'region_name',
                'city_name',
                'latitude',
                'longitude',
                'location_updated_at'
            ]);
        });
    }
};
