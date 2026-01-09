<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('creator_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birthday');
            $table->string('address');
            $table->string('city');
            $table->string('country');
            $table->string('state');
            $table->string('zip_code');
            $table->string('document_type');
            $table->string('front_id');
            $table->string('back_id')->nullable();
            $table->string('holding_id')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('creator_applications');
    }
};