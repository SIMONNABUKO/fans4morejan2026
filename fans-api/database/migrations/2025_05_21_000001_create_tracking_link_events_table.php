<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop the table if it exists
        Schema::dropIfExists('tracking_link_events');

        Schema::create('tracking_link_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tracking_link_id')->constrained()->onDelete('cascade');
            $table->string('event_type'); // click, subscription, purchase
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer_url')->nullable();
            $table->string('referrer_domain')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Add index for faster lookups
            $table->index(['tracking_link_id', 'event_type', 'ip_address'], 'tracking_link_events_lookup');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tracking_link_events');
    }
}; 