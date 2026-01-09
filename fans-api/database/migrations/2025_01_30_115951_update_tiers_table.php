<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tiers', function (Blueprint $table) {
            $table->dropColumn('amount');
        
            $table->decimal('base_price', 8, 2)->after('subscription_benefits');
            $table->decimal('two_month_price', 8, 2)->nullable()->after('base_price');
            $table->decimal('three_month_price', 8, 2)->nullable()->after('two_month_price');
            $table->decimal('six_month_price', 8, 2)->nullable()->after('three_month_price');
            $table->unsignedTinyInteger('two_month_discount')->default(0)->after('six_month_price');
            $table->unsignedTinyInteger('three_month_discount')->default(0)->after('two_month_discount');
            $table->unsignedTinyInteger('six_month_discount')->default(0)->after('three_month_discount');
            $table->json('active_plans')->after('six_month_discount');
            $table->boolean('subscriptions_enabled')->default(true)->after('active_plans');
            $table->text('description')->nullable()->after('subscriptions_enabled');
        });
    }

    public function down()
    {
        Schema::table('tiers', function (Blueprint $table) {
            $table->dropColumn([
                'base_price', 'two_month_price', 'three_month_price', 'six_month_price',
                'two_month_discount', 'three_month_discount', 'six_month_discount',
                'active_plans', 'subscriptions_enabled', 'description'
            ]);
            
            $table->decimal('amount', 8, 2)->after('subscription_benefits');
        });
    }
};

