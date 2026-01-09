<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'subject',
        'content',
        'media',
        'is_global',
        'usage_count'
    ];

    protected $casts = [
        'media' => 'array',
        'is_global' => 'boolean',
        'usage_count' => 'integer'
    ];

    /**
     * Get the user who owns the template
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Check if template has media
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
     * Scope for global templates
     */
    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    /**
     * Scope for user's own templates
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for available templates (global + user's own)
     */
    public function scopeAvailableFor($query, int $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('is_global', true)
              ->orWhere('user_id', $userId);
        });
    }

    /**
     * Scope for popular templates (by usage)
     */
    public function scopePopular($query, int $limit = 10)
    {
        return $query->orderBy('usage_count', 'desc')
                    ->limit($limit);
    }

    /**
     * Scope to search templates
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('subject', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%");
        });
    }

    /**
     * Convert template to message data format
     */
    public function toMessageData(): array
    {
        return [
            'subject' => $this->subject,
            'content' => $this->content,
            'media' => $this->media
        ];
    }

    /**
     * Get template summary
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'subject' => $this->subject,
            'content_preview' => \Illuminate\Support\Str::limit($this->content, 100),
            'media_count' => $this->getMediaCount(),
            'has_media' => $this->hasMedia(),
            'is_global' => $this->is_global,
            'usage_count' => $this->usage_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
