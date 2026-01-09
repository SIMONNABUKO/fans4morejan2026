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
            // Add missing location columns if they don't exist
            if (!Schema::hasColumn('users', 'country_name')) {
                $table->string('country_name')->nullable()->after('country_code');
            }
            
            if (!Schema::hasColumn('users', 'region_name')) {
                $table->string('region_name')->nullable()->after('country_name');
            }
            
            if (!Schema::hasColumn('users', 'city_name')) {
                $table->string('city_name')->nullable()->after('region_name');
            }
            
            if (!Schema::hasColumn('users', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('city_name');
            }
            
            if (!Schema::hasColumn('users', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
            
            if (!Schema::hasColumn('users', 'location_updated_at')) {
                $table->timestamp('location_updated_at')->nullable()->after('longitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['country_name', 'region_name', 'city_name', 'latitude', 'longitude', 'location_updated_at'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
