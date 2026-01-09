<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('list_members', function (Blueprint $table) {
            if (!Schema::hasColumn('list_members', 'list_type_id')) {
                $table->unsignedBigInteger('list_type_id')->nullable();
            }

            $table->unsignedBigInteger('list_id')->nullable()->change();
            
            if (!Schema::hasColumn('list_members', 'list_type_id')) {
                $table->foreign('list_type_id')->references('id')->on('list_types')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('list_members', function (Blueprint $table) {
            $table->unsignedBigInteger('list_id')->nullable(false)->change();
            if (Schema::hasColumn('list_members', 'list_type_id')) {
                $table->dropForeign(['list_type_id']);
                $table->dropColumn('list_type_id');
            }
        });
    }
};