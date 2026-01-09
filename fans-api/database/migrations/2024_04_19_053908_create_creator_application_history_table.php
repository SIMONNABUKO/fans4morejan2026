<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('creator_application_history')) {
            Schema::create('creator_application_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('application_id');
                $table->unsignedBigInteger('admin_id');
                $table->string('status');
                $table->text('feedback')->nullable();
                $table->timestamp('processed_at');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('creator_application_history');
    }
}; 