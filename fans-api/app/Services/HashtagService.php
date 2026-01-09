<?php

namespace App\Services;

use App\Models\Hashtag;
use App\Models\Post;
use Illuminate\Support\Str;

class HashtagService
{
    /**
     * Extract hashtags from text content
     */
    public function extractHashtags(string $content): array
    {
        preg_match_all('/#(\w+)/', $content, $matches);
        return array_unique(array_map('strtolower', $matches[1] ?? []));
    }

    /**
     * Create or find hashtags from names
     */
    public function findOrCreateHashtags(array $hashtagNames): array
    {
        $hashtags = [];
        
        foreach ($hashtagNames as $name) {
            $hashtags[] = Hashtag::firstOrCreate([
                'name' => strtolower($name)
            ], [
                'slug' => Str::slug($name)
            ]);
        }
        
        return $hashtags;
    }

    /**
     * Sync hashtags for a post
     */
    public function syncPostHashtags(Post $post): void
    {
        $hashtagNames = $this->extractHashtags($post->content);
        $hashtags = $this->findOrCreateHashtags($hashtagNames);
        
        $post->hashtags()->sync(collect($hashtags)->pluck('id'));
        
        // Update hashtag post counts
        $this->updateHashtagCounts($hashtags);
    }

    /**
     * Update post counts for hashtags
     */
    public function updateHashtagCounts(array $hashtags): void
    {
        foreach ($hashtags as $hashtag) {
            $hashtag->posts_count = $hashtag->posts()->count();
            $hashtag->save();
        }
    }

    /**
     * Search posts by hashtag
     */
    public function searchPostsByHashtag(string $hashtagName, $user = null, $perPage = 15)
    {
        $hashtag = Hashtag::where('name', strtolower($hashtagName))->first();
        
        if (!$hashtag) {
            // Return empty paginator instead of collection
            return Post::where('id', 0)->paginate($perPage);
        }

        $query = $hashtag->posts()
            ->with(['user', 'media.previews', 'hashtags', 'stats'])
            ->where('status', Post::STATUS_PUBLISHED)
            ->orderBy('created_at', 'desc');

        // Apply permission checks if user is provided
        if ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id) // User's own posts
                  ->orWhereHas('permissionSets', function ($pq) use ($user) {
                      // Posts user has permission to view
                      $pq->whereHas('permissions', function ($ppq) use ($user) {
                          $ppq->where('type', 'subscription')
                              ->whereIn('value', $user->subscriptions()->pluck('creator_id'));
                      });
                  })
                  ->orWhereDoesntHave('permissionSets'); // Public posts
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get popular hashtags
     */
    public function getPopularHashtags(int $limit = 10): array
    {
        return Hashtag::popular($limit)->get()->toArray();
    }

    /**
     * Get trending hashtags
     */
    public function getTrendingHashtags(int $limit = 10): array
    {
        return Hashtag::trending()->popular($limit)->get()->toArray();
    }

    /**
     * Search hashtags by name
     */
    public function searchHashtags(string $query, int $limit = 10): array
    {
        return Hashtag::search($query)
            ->orderBy('posts_count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get hashtag suggestions based on content
     */
    public function getHashtagSuggestions(string $content, int $limit = 5): array
    {
        $existingHashtags = $this->extractHashtags($content);
        $suggestions = Hashtag::popular($limit * 2)
            ->whereNotIn('name', $existingHashtags)
            ->limit($limit)
            ->get();
            
        return $suggestions->toArray();
    }
} 