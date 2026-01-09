<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('blocked_locations', function (Blueprint $table) {
            // Check if columns exist before adding them
            if (!Schema::hasColumn('blocked_locations', 'location_type')) {
                $table->string('location_type')->default('country')->after('country_name');
            }
            
            if (!Schema::hasColumn('blocked_locations', 'region_name')) {
                $table->string('region_name')->nullable()->after('location_type');
            }
            
            if (!Schema::hasColumn('blocked_locations', 'city_name')) {
                $table->string('city_name')->nullable()->after('region_name');
            }
            
            if (!Schema::hasColumn('blocked_locations', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('city_name');
            }
            
            if (!Schema::hasColumn('blocked_locations', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
            
            if (!Schema::hasColumn('blocked_locations', 'display_name')) {
                $table->string('display_name')->nullable()->after('longitude');
            }
        });

        // Update existing records to have location_type = 'country'
        DB::table('blocked_locations')
            ->whereNull('location_type')
            ->update(['location_type' => 'country']);

        // Drop the old unique constraint if it exists
        try {
            Schema::table('blocked_locations', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'country_code']);
            });
        } catch (\Exception $e) {
            // Constraint might not exist, continue
        }

        // Add the new unique constraint
        try {
            Schema::table('blocked_locations', function (Blueprint $table) {
                $table->unique(['user_id', 'country_code', 'location_type', 'region_name', 'city_name'], 'unique_location_block');
            });
        } catch (\Exception $e) {
            // Constraint might already exist, continue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new unique constraint
        try {
            Schema::table('blocked_locations', function (Blueprint $table) {
                $table->dropUnique('unique_location_block');
            });
        } catch (\Exception $e) {
            // Constraint might not exist, continue
        }

        // Restore the old unique constraint
        try {
            Schema::table('blocked_locations', function (Blueprint $table) {
                $table->unique(['user_id', 'country_code']);
            });
        } catch (\Exception $e) {
            // Constraint might already exist, continue
        }

        // Remove the new columns
        Schema::table('blocked_locations', function (Blueprint $table) {
            $columns = ['location_type', 'region_name', 'city_name', 'latitude', 'longitude', 'display_name'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('blocked_locations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
