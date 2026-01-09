<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ccbill_configs', function (Blueprint $table) {
            $table->id();
            $table->string('ccbill_account_number')->nullable();
            $table->string('ccbill_subaccount_number_recurring')->nullable();
            $table->string('ccbill_subaccount_number_one_time')->nullable();
            $table->string('ccbill_flex_form_id')->nullable();
            $table->string('ccbill_salt_key')->nullable();
            $table->string('ccbill_datalink_username')->nullable();
            $table->string('ccbill_datalink_password')->nullable();
            $table->timestamps();
        });

        $ccbillConfig = [
            'ccbill_account_number' => '954172',
            'ccbill_subaccount_number_recurring' => '0000',
            'ccbill_subaccount_number_one_time' => '0001',
            'ccbill_flex_form_id' => '22917272-2244-4532-a4dc-942c2b638f5a',
            'ccbill_salt_key' => 'RsQfsnA4tSFNO88VPJKT74xd',
            'ccbill_datalink_username' => 'FFMdl1',
            'ccbill_datalink_password' => 'rY0!uU4!'
        ];
        DB::table('ccbill_configs')->insert($ccbillConfig);
    }

    public function down()
    {
        Schema::dropIfExists('ccbill_configs');
    }
};

