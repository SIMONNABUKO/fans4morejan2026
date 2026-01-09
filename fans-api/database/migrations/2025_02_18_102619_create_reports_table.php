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
        if (!Schema::hasTable('reports')) {
            Schema::create('reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('content_type');
                $table->unsignedBigInteger('content_id');
                $table->string('reason');
                $table->text('additional_info')->nullable();
                $table->string('status')->default('pending');
                $table->timestamps();

                $table->index(['content_type', 'content_id']);
                $table->unique(['user_id', 'content_type', 'content_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
