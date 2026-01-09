<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'content',
        'read_at',
        'visible',
        'status',
        'reviewed_at',
        'reviewed_by',
        'transaction_id',
        'is_premium',
        'price'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'is_premium' => 'boolean',
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'visible' => 'boolean'
    ];

    // Message statuses
    const STATUS_ACTIVE = 'active';
    const STATUS_REPORTED = 'reported';
    const STATUS_BLOCKED = 'blocked';

    /**
     * Get the sender of the message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver of the message.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the reviewer of the message.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the media for the message.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Get the permission sets for the message.
     */
    public function permissionSets()
    {
        return $this->morphMany(PermissionSet::class, 'permissionable');
    }

    /**
     * Get all purchases for this message.
     */
    public function purchases()
    {
        return $this->morphMany(Purchase::class, 'purchasable');
    }

    /**
     * Get all tips for this message.
     */
    public function tips()
    {
        return $this->morphMany(Tip::class, 'tippable');
    }

    /**
     * Get the transaction associated with this message.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get all transactions for this message
     */
    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'purchasable');
    }

    /**
     * Check if the message requires payment based on creator settings
     */
    public function requiresPayment(): bool
    {
        // Get creator's DM settings
        $dmPrice = $this->receiver->getSetting('messaging', 'dm_price');
        return !empty($dmPrice) && $dmPrice > 0;
    }

    /**
     * Get the price for this message based on creator settings
     */
    public function getPrice(): ?float
    {
        if (!$this->requiresPayment()) {
            return null;
        }
        return $this->receiver->getSetting('messaging', 'dm_price');
    }

    /**
     * Check if the message has been purchased by a user
     */
    public function isPurchasedBy(User $user): bool
    {
        // If message doesn't require payment, it's accessible
        if (!$this->requiresPayment()) {
            return true;
        }

        // If sender is the creator, they can access it
        if ($user->id === $this->receiver_id) {
            return true;
        }

        // Check if user has an approved transaction for this message
        return $this->transactions()
            ->where('sender_id', $user->id)
            ->where('status', Transaction::APPROVED_STATUS)
            ->exists();
    }

    /**
     * For backward compatibility - get message purchases through the new purchases relationship
     */
    public function messagePurchases()
    {
        return $this->purchases();
    }

    /**
     * Check if a user has permission to view this message
     */
    public function hasPermission($user, $requiredPermission = null)
    {
        $permissionService = app(\App\Services\PermissionService::class);
        return $permissionService->checkPermission($this, $user, $requiredPermission);
    }

    /**
     * Check if this is a paid message
     */
    public function isPaid(): bool
    {
        return $this->transactions()
            ->where('status', Transaction::APPROVED_STATUS)
            ->exists();
    }

    /**
     * Scope query to only include active messages
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope query to only include reported messages
     */
    public function scopeReported($query)
    {
        return $query->where('status', self::STATUS_REPORTED);
    }

    /**
     * Scope query to only include blocked messages
     */
    public function scopeBlocked($query)
    {
        return $query->where('status', self::STATUS_BLOCKED);
    }
}

