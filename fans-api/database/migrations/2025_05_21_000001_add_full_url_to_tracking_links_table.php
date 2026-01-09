<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tracking_links', function (Blueprint $table) {
            if (!Schema::hasColumn('tracking_links', 'full_url')) {
                $table->string('full_url')->after('description')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('tracking_links', function (Blueprint $table) {
            if (Schema::hasColumn('tracking_links', 'full_url')) {
                $table->dropColumn('full_url');
            }
        });
    }
}; 