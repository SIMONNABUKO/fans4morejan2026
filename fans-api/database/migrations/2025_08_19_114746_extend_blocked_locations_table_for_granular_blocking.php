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
        Schema::table('blocked_locations', function (Blueprint $table) {
            // Add new fields for granular location blocking
            $table->string('location_type')->default('country')->after('country_name'); // country, region, city
            $table->string('region_name')->nullable()->after('location_type');
            $table->string('city_name')->nullable()->after('region_name');
            $table->decimal('latitude', 10, 8)->nullable()->after('city_name');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->string('display_name')->nullable()->after('longitude'); // Full display name from search
            
            // Update unique constraint to allow multiple entries per country but prevent exact duplicates
            $table->dropUnique(['user_id', 'country_code']);
            $table->unique(['user_id', 'country_code', 'location_type', 'region_name', 'city_name'], 'unique_location_block');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blocked_locations', function (Blueprint $table) {
            // Remove new fields
            $table->dropColumn([
                'location_type',
                'region_name', 
                'city_name',
                'latitude',
                'longitude',
                'display_name'
            ]);
            
            // Restore original unique constraint
            $table->dropUnique('unique_location_block');
            $table->unique(['user_id', 'country_code']);
        });
    }
};
