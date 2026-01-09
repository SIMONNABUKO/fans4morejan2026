<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_balance',
        'pending_balance',
        'available_for_payout',
    ];

    protected $casts = [
        'total_balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'available_for_payout' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addToTotalBalance($amount)
    {
        $this->total_balance += $amount;
        $this->save();
    }

    public function addToPendingBalance($amount)
    {
        $this->pending_balance += $amount;
        $this->save();
    }

    public function addToAvailableForPayout($amount)
    {
        $this->available_for_payout += $amount;
        $this->save();
    }

    public function subtractFromTotalBalance($amount)
    {
        $this->total_balance -= $amount;
        $this->save();
    }

    public function subtractFromPendingBalance($amount)
    {
        $this->pending_balance -= $amount;
        $this->save();
    }

    public function subtractFromAvailableForPayout($amount)
    {
        $this->available_for_payout -= $amount;
        $this->save();
    }

    public function movePendingToAvailable($amount)
    {
        $this->pending_balance -= $amount;
        $this->available_for_payout += $amount;
        $this->save();
    }
}

