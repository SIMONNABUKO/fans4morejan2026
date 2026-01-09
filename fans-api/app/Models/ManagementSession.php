<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagementSession extends Model
{
    protected $fillable = [
        'name',
        'token',
        'owner_id',
        'claimed_by',
        'used_at',
        'expires_at',
        'device_name',
        'device_type',
        'browser',
        'platform',
        'ip_address',
        'location',
    ];

    protected $casts = [
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the owner of the management session
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the user who claimed this session
     */
    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    /**
     * Check if the session has been used
     */
    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }

    /**
     * Check if the session has expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the session is valid (not used and not expired)
     */
    public function isValid(): bool
    {
        return !$this->isUsed() && !$this->isExpired();
    }
}
