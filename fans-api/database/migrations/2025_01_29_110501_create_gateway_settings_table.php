<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('gateway_settings', function (Blueprint $table) {
            $table->id();
            $table->string('active_gateway')->default('ccbill');
            $table->decimal('refund_fee', 8, 2)->default(25);
            $table->integer('platform_fee_percentage')->default(15);
            $table->timestamps();
        });
        DB::table('gateway_settings')->insert([
            'active_gateway' => 'ccbill',
            'refund_fee' => 25,
            'platform_fee_percentage' => 15,
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('gateway_settings');
    }
};
