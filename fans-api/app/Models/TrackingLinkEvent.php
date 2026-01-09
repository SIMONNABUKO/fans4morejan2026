<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingLinkEvent extends Model
{
    protected $fillable = [
        'tracking_link_id',
        'event_type',
        'ip_address',
        'user_agent',
        'referrer_url',
        'referrer_domain',
        'user_id',
        'subscription_id',
        'transaction_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function trackingLink(): BelongsTo
    {
        return $this->belongsTo(TrackingLink::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
} 