<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformWallet extends Model
{
    protected $table = 'platform_wallet';
    
    protected $fillable = [
        'balance'
    ];

    public function history()
    {
        return $this->hasMany(PlatformWalletHistory::class);
    }

    public function addFunds($amount, $transactionId = null, $description = 'Platform fee collected')
    {
        $this->balance += $amount;
        $this->save();

        // Record history
        PlatformWalletHistory::create([
            'amount' => $amount,
            'transaction_type' => 'CREDIT',
            'description' => $description,
            'transaction_id' => $transactionId,
        ]);
    }

    public function subtractFunds($amount, $transactionId = null, $description = 'Funds withdrawn')
    {
        if ($this->balance < $amount) {
            throw new \Exception('Insufficient platform wallet balance');
        }

        $this->balance -= $amount;
        $this->save();

        // Record history
        PlatformWalletHistory::create([
            'amount' => $amount,
            'transaction_type' => 'DEBIT',
            'description' => $description,
            'transaction_id' => $transactionId,
        ]);
    }
} 