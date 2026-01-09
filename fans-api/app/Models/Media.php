<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\MediaStorageService;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mediable_id',
        'mediable_type',
        'type',
        'url',
        'status'
    ];

    protected $appends = ['full_url'];

    // Add status constants for better code readability
    const STATUS_ACTIVE = 'active';
    const STATUS_PENDING = 'pending';
    const STATUS_FLAGGED = 'flagged';
    const STATUS_REMOVED = 'removed';

    public function mediable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function previews()
    {
        return $this->hasMany(MediaPreview::class);
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function bookmarks()
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    public function stats()
    {
        return $this->morphOne(Stat::class, 'statable');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'mediable_id')->where('mediable_type', Post::class);
    }

    public function getUrlAttribute($value)
    {
        return app(MediaStorageService::class)->url($value);
    }

    // Add this accessor method that just returns the existing URL
    public function getFullUrlAttribute()
    {
        return app(MediaStorageService::class)->url($this->getRawOriginal('url'));
    }
    public function isLikedByUser(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function isBookmarkedByUser(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $this->bookmarks()->where('user_id', $user->id)->exists();
    }
}
