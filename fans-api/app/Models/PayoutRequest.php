<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payout_method_id',
        'amount',
        'status',
        'reference_id',
        'processed_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_PROCESSED = 'processed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payoutMethod()
    {
        return $this->belongsTo(PayoutMethod::class);
    }

    public function walletHistories()
    {
        return $this->morphMany(WalletHistory::class, 'transactionable');
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessed()
    {
        return $this->status === self::STATUS_PROCESSED;
    }

    public function isFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function generateReferenceId()
    {
        return time() . rand(1000000, 9999999);
    }
}