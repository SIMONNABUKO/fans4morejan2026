<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ScheduledMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'subject',
        'content',
        'recipients',
        'media',
        'scheduled_for',
        'timezone',
        'recurring_type',
        'recurring_end_date',
        'delivery_options',
        'status',
        'mass_message_id',
        'sent_at',
        'failure_reason',
        'analytics'
    ];

    protected $casts = [
        'recipients' => 'array',
        'media' => 'array',
        'delivery_options' => 'array',
        'analytics' => 'array',
        'scheduled_for' => 'datetime',
        'recurring_end_date' => 'date',
        'sent_at' => 'datetime'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FAILED = 'failed';

    // Recurring type constants
    const RECURRING_DAILY = 'daily';
    const RECURRING_WEEKLY = 'weekly';
    const RECURRING_MONTHLY = 'monthly';

    /**
     * Get the sender of the scheduled message
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the associated mass message campaign
     */
    public function massMessage(): BelongsTo
    {
        return $this->belongsTo(MassMessage::class, 'mass_message_id');
    }

    /**
     * Scope to get messages ready to be sent
     */
    public function scopeReadyToSend($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->where('scheduled_for', '<=', now());
    }

    /**
     * Scope to get recurring messages
     */
    public function scopeRecurring($query)
    {
        return $query->whereNotNull('recurring_type');
    }

    /**
     * Check if message is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_PENDING && 
               $this->scheduled_for < now()->subHours(1);
    }

    /**
     * Check if message is recurring
     */
    public function isRecurring(): bool
    {
        return !is_null($this->recurring_type);
    }

    /**
     * Get the next occurrence for recurring messages
     */
    public function getNextOccurrence(): ?Carbon
    {
        if (!$this->isRecurring()) {
            return null;
        }

        $next = $this->scheduled_for->copy();

        switch ($this->recurring_type) {
            case self::RECURRING_DAILY:
                $next->addDay();
                break;
            case self::RECURRING_WEEKLY:
                $next->addWeek();
                break;
            case self::RECURRING_MONTHLY:
                $next->addMonth();
                break;
        }

        // Check if next occurrence is past end date
        if ($this->recurring_end_date && $next->gt($this->recurring_end_date)) {
            return null;
        }

        return $next;
    }

    /**
     * Mark message as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now()
        ]);
    }

    /**
     * Mark message as failed
     */
    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'failure_reason' => $reason
        ]);
    }

    /**
     * Get recipient count
     */
    public function getRecipientCount(): int
    {
        if (!is_array($this->recipients)) {
            return 0;
        }

        return count($this->recipients['users'] ?? []);
    }

    /**
     * Get formatted scheduling info
     */
    public function getSchedulingInfo(): array
    {
        return [
            'scheduled_for' => $this->scheduled_for,
            'timezone' => $this->timezone,
            'is_recurring' => $this->isRecurring(),
            'recurring_type' => $this->recurring_type,
            'recurring_end_date' => $this->recurring_end_date,
            'next_occurrence' => $this->getNextOccurrence()
        ];
    }
}
