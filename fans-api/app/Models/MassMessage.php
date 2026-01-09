<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MassMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'subject',
        'content',
        'recipients_data',
        'media',
        'permissions',
        'delivery_options',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'failed_count',
        'opened_count',
        'clicked_count',
        'status',
        'type',
        'started_at',
        'completed_at',
        'analytics'
    ];

    protected $casts = [
        'recipients_data' => 'array',
        'media' => 'array',
        'permissions' => 'array',
        'delivery_options' => 'array',
        'analytics' => 'array',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'delivered_count' => 'integer',
        'failed_count' => 'integer',
        'opened_count' => 'integer',
        'clicked_count' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SENDING = 'sending';
    const STATUS_SENT = 'sent';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FAILED = 'failed';

    // Type constants
    const TYPE_IMMEDIATE = 'immediate';
    const TYPE_SCHEDULED = 'scheduled';
    const TYPE_RECURRING = 'recurring';

    /**
     * Get the sender of the mass message
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get scheduled messages associated with this mass message
     */
    public function scheduledMessages(): HasMany
    {
        return $this->hasMany(ScheduledMessage::class, 'mass_message_id');
    }

    /**
     * Scope for active campaigns
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_DRAFT,
            self::STATUS_SENDING
        ]);
    }

    /**
     * Scope for completed campaigns
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('status', [
            self::STATUS_SENT,
            self::STATUS_CANCELLED,
            self::STATUS_FAILED
        ]);
    }

    /**
     * Mark as sending
     */
    public function markAsSending(): void
    {
        $this->update([
            'status' => self::STATUS_SENDING,
            'started_at' => now()
        ]);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'completed_at' => now()
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'completed_at' => now()
        ]);
    }

    /**
     * Update send statistics
     */
    public function updateStats(int $sent = 0, int $delivered = 0, int $failed = 0): void
    {
        $this->increment('sent_count', $sent);
        $this->increment('delivered_count', $delivered);
        $this->increment('failed_count', $failed);

        // Check if sending is complete
        if (($this->sent_count + $this->failed_count) >= $this->total_recipients) {
            $this->markAsCompleted();
        }
    }

    /**
     * Update engagement statistics
     */
    public function updateEngagementStats(int $opened = 0, int $clicked = 0): void
    {
        $this->increment('opened_count', $opened);
        $this->increment('clicked_count', $clicked);
    }

    /**
     * Get delivery rate
     */
    public function getDeliveryRate(): float
    {
        if ($this->sent_count === 0) {
            return 0;
        }

        return ($this->delivered_count / $this->sent_count) * 100;
    }

    /**
     * Get open rate
     */
    public function getOpenRate(): float
    {
        if ($this->delivered_count === 0) {
            return 0;
        }

        return ($this->opened_count / $this->delivered_count) * 100;
    }

    /**
     * Get click rate
     */
    public function getClickRate(): float
    {
        if ($this->opened_count === 0) {
            return 0;
        }

        return ($this->clicked_count / $this->opened_count) * 100;
    }

    /**
     * Get progress percentage
     */
    public function getProgress(): float
    {
        if ($this->total_recipients === 0) {
            return 0;
        }

        return (($this->sent_count + $this->failed_count) / $this->total_recipients) * 100;
    }

    /**
     * Check if campaign is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_SENDING;
    }

    /**
     * Check if campaign is completed
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, [
            self::STATUS_SENT,
            self::STATUS_CANCELLED,
            self::STATUS_FAILED
        ]);
    }

    /**
     * Get campaign statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_recipients' => $this->total_recipients,
            'sent_count' => $this->sent_count,
            'delivered_count' => $this->delivered_count,
            'failed_count' => $this->failed_count,
            'opened_count' => $this->opened_count,
            'clicked_count' => $this->clicked_count,
            'delivery_rate' => round($this->getDeliveryRate(), 2),
            'open_rate' => round($this->getOpenRate(), 2),
            'click_rate' => round($this->getClickRate(), 2),
            'progress' => round($this->getProgress(), 2),
            'status' => $this->status,
            'type' => $this->type,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at
        ];
    }

    /**
     * Get campaign summary
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'content_preview' => \Illuminate\Support\Str::limit($this->content, 100),
            'total_recipients' => $this->total_recipients,
            'status' => $this->status,
            'type' => $this->type,
            'progress' => $this->getProgress(),
            'delivery_rate' => $this->getDeliveryRate(),
            'open_rate' => $this->getOpenRate(),
            'created_at' => $this->created_at,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at
        ];
    }
}
