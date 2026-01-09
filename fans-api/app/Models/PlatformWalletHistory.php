<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformWalletHistory extends Model
{
    protected $table = 'platform_wallet_history';
    
    protected $fillable = [
        'amount',
        'transaction_type',
        'description',
        'transaction_id',
        'fee_type',
        'original_amount',
        'fee_percentage',
        'sender_id',
        'receiver_id',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'fee_percentage' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function platformWallet()
    {
        return $this->belongsTo(PlatformWallet::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
} 