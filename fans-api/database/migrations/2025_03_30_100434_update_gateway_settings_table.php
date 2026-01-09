<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('gateway_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('gateway_settings', 'test_mode')) {
                $table->boolean('test_mode')->default(false)->after('active_gateway');
            }
        });
    }

    public function down()
    {
        Schema::table('gateway_settings', function (Blueprint $table) {
            if (Schema::hasColumn('gateway_settings', 'test_mode')) {
                $table->dropColumn('test_mode');
            }
        });
    }
};