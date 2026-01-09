<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('creator_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('creator_applications', 'feedback')) {
                $table->text('feedback')->nullable()->after('status');
            }
        });
    }

    public function down()
    {
        Schema::table('creator_applications', function (Blueprint $table) {
            if (Schema::hasColumn('creator_applications', 'feedback')) {
                $table->dropColumn('feedback');
            }
        });
    }
}; 