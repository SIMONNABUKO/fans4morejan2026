<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CcbillConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'ccbill_account_number',
        'ccbill_subaccount_number_recurring',
        'ccbill_subaccount_number_one_time',
        'ccbill_flex_form_id',
        'ccbill_salt_key',
        'ccbill_datalink_username',
        'ccbill_datalink_password',
    ];

    protected $hidden = [
        'ccbill_salt_key',
        'ccbill_datalink_password',
    ];
}

