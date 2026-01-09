<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\PermissionService;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'status',
        'moderation_note',
        'scheduled_for',
        'expires_at',
        'pinned',
        'pinned_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'expires_at' => 'datetime',
        'pinned' => 'boolean',
        'pinned_at' => 'datetime',
    ];
    
    // Add status constants for better code readability
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_PUBLISHED = 'published';
    const STATUS_REJECTED = 'rejected';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function permissionSets()
    {
        return $this->morphMany(PermissionSet::class, 'permissionable');
    }

    /**
     * Get all purchases for this post.
     */
    public function purchases()
    {
        return $this->morphMany(Purchase::class, 'purchasable');
    }

    /**
     * Get all tips for this post.
     */
    public function tips()
    {
        return $this->morphMany(Tip::class, 'tippable');
    }

    /**
     * Check if a user has purchased this post.
     */
    public function isPurchasedBy($userId)
    {
        return $this->purchases()->where('user_id', $userId)->exists();
    }

    /**
     * For backward compatibility - get post purchases through the new purchases relationship
     */
    public function postPurchases()
    {
        return $this->purchases();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
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
    
    // Add relationship to tagged users
    public function tags()
    {
        return $this->hasMany(PostTag::class);
    }
    
    // Get all users tagged in this post
    public function taggedUsers()
    {
        return $this->belongsToMany(User::class, 'post_tags', 'post_id', 'user_id')
            ->withPivot('status')
            ->withTimestamps();
    }
    
    // Get only approved tags
    public function approvedTags()
    {
        return $this->tags()->where('status', 'approved');
    }
    
    // Get only pending tags
    public function pendingTags()
    {
        return $this->tags()->where('status', 'pending');
    }
    
    // Check if post is ready to be published (all tags approved or no tags)
    public function isReadyToPublish()
    {
        return $this->pendingTags()->count() === 0;
    }
    
    // Add hashtag relationships
    public function hashtags()
    {
        return $this->belongsToMany(Hashtag::class, 'post_hashtags')
            ->withTimestamps();
    }
    
    // Process content to add user tag links
    public function processedContent()
    {
        $content = $this->content;
        
        // Get all approved tagged users
        $taggedUsers = $this->taggedUsers()
            ->wherePivot('status', 'approved')
            ->get();
            
        foreach ($taggedUsers as $user) {
            // Replace @username with linked username
            $content = preg_replace(
                '/@' . preg_quote($user->username) . '\b/',
                '<a href="/profile/' . $user->username . '">@' . $user->username . '</a>',
                $content
            );
        }
        
        return $content;
    }

    public function hasPermission($user, $requiredPermission = null)
    {
        $permissionService = app(PermissionService::class);
        return $permissionService->checkPermission($this, $user, $requiredPermission);
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

    /**
     * Pin the post
     */
    public function pin(): void
    {
        $this->update([
            'pinned' => true,
            'pinned_at' => now(),
        ]);
    }

    /**
     * Unpin the post
     */
    public function unpin(): void
    {
        $this->update([
            'pinned' => false,
            'pinned_at' => null,
        ]);
    }

    /**
     * Check if the post is pinned
     */
    public function isPinned(): bool
    {
        return $this->pinned;
    }
    
    /**
     * Extract hashtags from post content
     */
    public function extractHashtags()
    {
        preg_match_all('/#(\w+)/', $this->content, $matches);
        return $matches[1] ?? [];
    }
    
    /**
     * Sync hashtags for this post
     */
    public function syncHashtags()
    {
        $hashtagNames = $this->extractHashtags();
        $hashtagIds = [];
        
        foreach ($hashtagNames as $hashtagName) {
            $hashtag = Hashtag::firstOrCreate(['name' => strtolower($hashtagName)]);
            $hashtagIds[] = $hashtag->id;
        }
        
        $this->hashtags()->sync($hashtagIds);
        
        // Update hashtag post counts
        Hashtag::whereIn('id', $hashtagIds)->each(function ($hashtag) {
            $hashtag->posts_count = $hashtag->posts()->count();
            $hashtag->save();
        });
    }
}

