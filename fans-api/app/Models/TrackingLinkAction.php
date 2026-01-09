<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingLinkAction extends Model
{
    protected $fillable = [
        'tracking_link_id',
        'action_type',
        'user_id',
        'action_data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'action_data' => 'array',
    ];

    public function trackingLink(): BelongsTo
    {
        return $this->belongsTo(TrackingLink::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
