<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('list_members', function (Blueprint $table) {
            $table->unsignedBigInteger('list_type_id')->nullable()->after('list_id');
            $table->foreign('list_type_id')->references('id')->on('list_types')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('list_members', function (Blueprint $table) {
            $table->dropForeign(['list_type_id']);
            $table->dropColumn('list_type_id');
        });
    }
};
