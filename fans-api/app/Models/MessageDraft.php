<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MessageDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'content',
        'recipients',
        'media',
        'delivery_settings',
        'draft_name'
    ];

    protected $casts = [
        'recipients' => 'array',
        'media' => 'array',
        'delivery_settings' => 'array'
    ];

    /**
     * Get the user who owns the draft
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
     * Check if draft has media attachments
     */
    public function hasMedia(): bool
    {
        return is_array($this->media) && count($this->media) > 0;
    }

    /**
     * Get media count
     */
    public function getMediaCount(): int
    {
        if (!is_array($this->media)) {
            return 0;
        }

        return count($this->media);
    }

    /**
     * Get draft summary
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'content_preview' => Str::limit($this->content, 100),
            'recipient_count' => $this->getRecipientCount(),
            'media_count' => $this->getMediaCount(),
            'has_media' => $this->hasMedia(),
            'draft_name' => $this->draft_name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }

    /**
     * Scope to get recent drafts
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('updated_at', 'desc')->limit($limit);
    }

    /**
     * Scope to search drafts by content or subject
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('subject', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%")
              ->orWhere('draft_name', 'like', "%{$search}%");
        });
    }

    /**
     * Convert draft to message data format
     */
    public function toMessageData(): array
    {
        return [
            'subject' => $this->subject,
            'content' => $this->content,
            'recipients' => $this->recipients,
            'media' => $this->media,
            'delivery' => $this->delivery_settings
        ];
    }
}
