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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->string('username')->unique();
            $table->string('handle')->unique();
            $table->string('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->string('cover_photo')->nullable();
            $table->enum('role', ['admin', 'creator', 'user'])->default('user');
            $table->integer('support_count')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('media_watermark')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->unsignedBigInteger('google_id')->nullable()->unique();
            $table->string('password');
            $table->boolean('can_be_followed')->default(true);
            $table->boolean('is_online')->default(false);
            $table->boolean('is_suspended')->default(false);
            $table->boolean('is_banned')->default(false);
            $table->longText('country_name')->nullable();
            $table->longText('country_code')->nullable();
            $table->longText('region_name')->nullable();
            $table->longText('city_name')->nullable();
            $table->longText('ip_address')->nullable();
            $table->longText('api_token')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('terms_accepted')->default(false);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
