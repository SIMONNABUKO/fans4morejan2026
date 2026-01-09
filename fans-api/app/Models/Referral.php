<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Referral extends Model
{
    protected $fillable = [
        'referrer_id',
        'referred_id',
        'referral_code',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the user who created the referral.
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get the user who was referred.
     */
    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    /**
     * Get the earnings associated with this referral.
     */
    public function earnings(): HasMany
    {
        return $this->hasMany(ReferralEarning::class);
    }

    /**
     * Scope a query to only include active referrals.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
} 