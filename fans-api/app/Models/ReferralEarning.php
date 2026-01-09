<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralEarning extends Model
{
    protected $fillable = [
        'referral_id',
        'amount',
        'type',
        'status',
        'transaction_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'type' => 'string',
        'status' => 'string',
    ];

    /**
     * Get the referral that owns the earning.
     */
    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    /**
     * Get the transaction associated with this earning.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Scope a query to only include pending earnings.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include paid earnings.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
} 