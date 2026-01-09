<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'amount',
        'type',
        'status',
        'tier_id',
        'ccbill_transaction_id',
        'ccbill_subscription_id',
        'ccbill_payment_token',
        'additional_data',
        'purchasable_type',
        'purchasable_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'additional_data' => 'array',
    ];

    // Transaction Types
    const ONE_TIME_PURCHASE = 'one_time_purchase';
    const ONE_MONTH_SUBSCRIPTION = 'one_month_subscription';
    const THREE_MONTHS_SUBSCRIPTION = 'three_months_subscription';
    const TWO_MONTHS_SUBSCRIPTION = 'two_months_subscription';
    const SIX_MONTHS_SUBSCRIPTION = 'six_months_subscription';
    const YEARLY_SUBSCRIPTION = 'yearly_subscription';
    const TIP = 'tip';
    const MESSAGE_PURCHASE = 'message_purchase';
    const SUBSCRIPTION_RENEWAL = 'subscription_renewal';

    // Transaction Statuses
    const PENDING_STATUS = 'pending';
    const APPROVED_STATUS = 'approved';
    const DECLINED_STATUS = 'declined';
    const REFUNDED_STATUS = 'refunded';

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function tier()
    {
        return $this->belongsTo(Tier::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'transaction_id', 'id');
    }

    /**
     * Get the purchasable model (message, tip, etc)
     */
    public function purchasable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopePending($query)
    {
        return $query->where('status', self::PENDING_STATUS);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::APPROVED_STATUS);
    }

    public function scopeDeclined($query)
    {
        return $query->where('status', self::DECLINED_STATUS);
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', self::REFUNDED_STATUS);
    }

    public function isSubscription()
    {
        return in_array($this->type, [
            self::ONE_MONTH_SUBSCRIPTION,
            self::TWO_MONTHS_SUBSCRIPTION,
            self::THREE_MONTHS_SUBSCRIPTION,
            self::SIX_MONTHS_SUBSCRIPTION,
            self::YEARLY_SUBSCRIPTION,
            self::SUBSCRIPTION_RENEWAL,
        ]);
    }

    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    public function getFormattedTypeAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->type));
    }
}

