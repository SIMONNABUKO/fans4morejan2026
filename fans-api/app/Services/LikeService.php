<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\BookmarkAlbum;
use App\Models\Media;
use App\Models\MediaPreview;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LikeService
{
    protected $bookmarkService;
    protected $statsService;
    protected $emailService;

    public function __construct(BookmarkService $bookmarkService, StatsService $statsService, EmailService $emailService)
    {
        $this->bookmarkService = $bookmarkService;
        $this->statsService = $statsService;
        $this->emailService = $emailService; 
    }

    public function like(Model $likeable, User $user): void
    {
        DB::transaction(function () use ($likeable, $user) {
            Log::info('Starting like process', [
                'model_type' => get_class($likeable),
                'model_id' => $likeable->id,
                'user_id' => $user->id
            ]);

            // Prevent duplicate likes
            if ($likeable->likes()->where('user_id', $user->id)->exists()) {
                Log::info('User already liked this item', [
                    'model_type' => get_class($likeable),
                    'model_id' => $likeable->id,
                    'user_id' => $user->id
                ]);
                return;
            }

            $likeable->likes()->create(['user_id' => $user->id]);
    
            Log::info('Incrementing likes for model', [
                'model_type' => get_class($likeable),
                'model_id' => $likeable->id,
                'user_id' => $user->id
            ]);
    
            $this->statsService->incrementLikes($likeable);

            if ($likeable instanceof Media || $likeable instanceof MediaPreview) {
                Log::info('Liking media or media preview', [
                    'model_type' => get_class($likeable),
                    'model_id' => $likeable->id
                ]);
                
                $likesAlbum = $this->getLikesAlbum($user);
                Log::info('Got Likes album', [
                    'album_id' => $likesAlbum->id,
                    'user_id' => $user->id
                ]);
                
                $this->bookmarkService->bookmark($likeable, $user, $likesAlbum);
                Log::info('Added media to Likes album', [
                    'model_id' => $likeable->id,
                    'album_id' => $likesAlbum->id
                ]);
                
                // If this is a media, also add its previews to the Likes album
                if ($likeable instanceof Media && method_exists($likeable, 'previews') && $likeable->previews->isNotEmpty()) {
                    foreach ($likeable->previews as $preview) {
                        Log::info('Adding media preview to Likes album', [
                            'preview_id' => $preview->id,
                            'album_id' => $likesAlbum->id
                        ]);
                        
                        $this->bookmarkService->bookmark($preview, $user, $likesAlbum);
                    }
                }
                
                $this->sendMediaLikeNotification($likeable, $user);
            } elseif ($likeable instanceof Post) {
                Log::info('Liking post', [
                    'post_id' => $likeable->id
                ]);
                
                // Bookmark the post to default "Posts" album
                $this->bookmarkService->bookmark($likeable, $user);
                Log::info('Added post to Posts album', [
                    'post_id' => $likeable->id
                ]);
                
                // Add all media from the post to the Likes album
                $this->addPostMediaToLikesAlbum($likeable, $user);
                $this->sendPostLikeNotification($likeable, $user);
            }

            Log::info('Like process completed', [
                'model_type' => get_class($likeable),
                'model_id' => $likeable->id,
                'user_id' => $user->id,
                'new_total_likes' => $likeable->stats->total_likes
            ]);
        });
    }

    public function unlike(Model $likeable, User $user): void
    {
        DB::transaction(function () use ($likeable, $user) {
            Log::info('Starting unlike process', [
                'model_type' => get_class($likeable),
                'model_id' => $likeable->id,
                'user_id' => $user->id
            ]);
            
            $likeable->likes()->where('user_id', $user->id)->delete();
            $this->statsService->decrementLikes($likeable);

            if ($likeable instanceof Media || $likeable instanceof MediaPreview) {
                $likesAlbum = $this->getLikesAlbum($user);
                $this->bookmarkService->unbookmark($likeable, $user, $likesAlbum);
                
                // If this is a media, also remove its previews from the Likes album
                if ($likeable instanceof Media && method_exists($likeable, 'previews') && $likeable->previews->isNotEmpty()) {
                    foreach ($likeable->previews as $preview) {
                        Log::info('Removing media preview from Likes album', [
                            'preview_id' => $preview->id,
                            'album_id' => $likesAlbum->id
                        ]);
                        
                        $this->bookmarkService->unbookmark($preview, $user, $likesAlbum);
                    }
                }
                
                Log::info('Removed media from Likes album', [
                    'model_id' => $likeable->id,
                    'album_id' => $likesAlbum->id
                ]);
            }
            
            Log::info('Unlike process completed', [
                'model_type' => get_class($likeable),
                'model_id' => $likeable->id,
                'user_id' => $user->id
            ]);
        });
    }

    public function isLiked(Model $likeable, User $user): bool
    {
        return $likeable->likes()->where('user_id', $user->id)->exists();
    }

    public function getLikesCount(Model $likeable): int
    {
        return $likeable->likes()->count();
    }

    protected function getLikesAlbum(User $user): BookmarkAlbum
    {
        $album = BookmarkAlbum::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Likes'],
            ['description' => 'Automatically created album for liked media and media previews']
        );
        
        Log::info('Retrieved or created Likes album', [
            'album_id' => $album->id,
            'user_id' => $user->id,
            'was_created' => $album->wasRecentlyCreated
        ]);
        
        return $album;
    }

    /**
     * Add all media from a post to the user's Likes album
     *
     * @param Post $post
     * @param User $user
     * @return void
     */
    protected function addPostMediaToLikesAlbum(Post $post, User $user): void
    {
        $likesAlbum = $this->getLikesAlbum($user);
        
        // Get all media associated with the post
        $mediaItems = $post->media;
        
        Log::info('Adding post media to Likes album', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'media_count' => $mediaItems->count(),
            'album_id' => $likesAlbum->id
        ]);
        
        if ($mediaItems->isNotEmpty()) {
            foreach ($mediaItems as $media) {
                try {
                    // Add the media to the album
                    $this->bookmarkService->bookmark($media, $user, $likesAlbum);
                    Log::info('Added media to Likes album', [
                        'media_id' => $media->id,
                        'album_id' => $likesAlbum->id
                    ]);
                    
                    // Also add any media previews if they exist
                    if (method_exists($media, 'previews') && $media->previews->isNotEmpty()) {
                        foreach ($media->previews as $preview) {
                            Log::info('Adding media preview to Likes album', [
                                'preview_id' => $preview->id,
                                'album_id' => $likesAlbum->id
                            ]);
                            
                            $this->bookmarkService->bookmark($preview, $user, $likesAlbum);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to add media to Likes album', [
                        'media_id' => $media->id,
                        'album_id' => $likesAlbum->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } else {
            Log::info('Post has no media to add to Likes album', [
                'post_id' => $post->id
            ]);
        }
    }

    protected function sendMediaLikeNotification(Model $media, User $liker): void
    {
        $owner = $media->user;
        if ($owner->id !== $liker->id) {
            $this->emailService->sendMediaLikeNotification($owner, [
                'liker_name' => $liker->name,
                'media_type' => $media instanceof Media ? 'media' : 'media preview',
                'media_id' => $media->id,
            ]);
        }
    }

    protected function sendPostLikeNotification(Post $post, User $liker): void
    {
        $owner = $post->user;
        if ($owner->id !== $liker->id) {
            $this->emailService->sendPostLikeNotification($owner, [
                'liker_name' => $liker->name,
                'post_id' => $post->id,
            ]);
        }
    }
}