<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users');
            $table->foreignId('recipient_id')->constrained('users');
            $table->string('tippable_type')->nullable();
            $table->unsignedBigInteger('tippable_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->timestamps();
            
            // Add index for polymorphic relationship
            $table->index(['tippable_type', 'tippable_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tips');
    }
};

