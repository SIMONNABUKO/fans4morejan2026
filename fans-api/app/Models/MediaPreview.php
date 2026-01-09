<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Services\MediaStorageService;

class MediaPreview extends Model
{
    protected $fillable = ['media_id', 'url'];
    protected $appends = ['full_url'];

    public function media()
    {
        return $this->belongsTo(Media::class);
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

    /**
     * This is a placeholder relationship to prevent errors.
     * MediaPreviews don't actually have previews themselves.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function previews()
    {
        // Return an empty relationship to prevent errors when eager loading
        return $this->hasMany(self::class, 'id', 'id')->whereNull('id');
    }

    /**
     * Get the user who owns this media preview.
     * This is a placeholder relationship that returns the user who owns the parent media.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function user()
    {
        // If the media preview belongs to a media item, get the user through that relationship
        return $this->hasOneThrough(
            User::class,
            Media::class,
            'id', // Foreign key on Media table
            'id', // Foreign key on User table
            'media_id', // Local key on MediaPreview table
            'user_id' // Local key on Media table
        );
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

    public function getUrlAttribute($value)
    {
        return app(MediaStorageService::class)->url($value);
    }

    public function getFullUrlAttribute()
    {
        return app(MediaStorageService::class)->url($this->getRawOriginal('url'));
    }
}
