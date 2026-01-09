<?php

namespace App\Repositories;

use App\Contracts\PostRepositoryInterface;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Models\Media;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostRepository implements PostRepositoryInterface
{
    public function create(array $data, User $user): Post
    {
        return DB::transaction(function () use ($data, $user) {
            Log::info('Creating new post', ['user_id' => $user->id, 'data' => $data]);

            $now = now();
            $scheduledFor = isset($data['scheduled_for']) ? $data['scheduled_for'] : null;
            $expiresAt = isset($data['expires_at']) ? $data['expires_at'] : null;

            // Determine status
            if ($scheduledFor && $scheduledFor > $now) {
                $status = Post::STATUS_PENDING ?? 'pending';
            } else {
                $status = Post::STATUS_PUBLISHED ?? 'published';
            }

            // Validate expires_at
            if ($expiresAt) {
                $compareDate = $scheduledFor ?: $now;
                if ($expiresAt <= $compareDate) {
                    $expiresAt = null;
                }
            }

            $post = Post::create([
                'user_id' => $user->id,
                'content' => $data['content'] ?? null,
                'status' => $status,
                'moderation_note' => $data['moderation_note'] ?? null,
                'scheduled_for' => $scheduledFor,
                'expires_at' => $expiresAt,
            ]);

            Log::info('Post created', ['post_id' => $post->id]);

            if (isset($data['media']) && is_array($data['media'])) {
                Log::info('Processing media for post', ['post_id' => $post->id, 'media_count' => count($data['media'])]);

                foreach ($data['media'] as $mediaItem) {
                    Log::info('Processing media item', ['media_item' => $mediaItem]);

                    if (isset($mediaItem['url'])) {
                        $media = new Media([
                            'user_id' => $user->id,
                            'type' => $mediaItem['type'],
                            'url' => $mediaItem['url'],
                        ]);
                        $post->media()->save($media);

                        Log::info('Media saved', ['media_id' => $media->id, 'post_id' => $post->id]);

                        if (isset($mediaItem['previews']) && is_array($mediaItem['previews'])) {
                            Log::info('Processing previews', ['media_id' => $media->id, 'preview_count' => count($mediaItem['previews'])]);

                            foreach ($mediaItem['previews'] as $previewUrl) {
                                if (is_string($previewUrl)) {
                                    $preview = $media->previews()->create(['url' => $previewUrl]);
                                    Log::info('Preview saved', ['preview_id' => $preview->id, 'media_id' => $media->id]);
                                }
                            }
                        }
                    }
                }
            }

            Log::info('Post creation completed', ['post_id' => $post->id]);

            return $post->load('media.previews');
        });
    }


    /**
     * Get posts newer than the specified post ID
     *
     * @param User $user The current user
     * @param Collection|array $followedUserIds Collection of user IDs or array of user IDs
     * @param int $lastPostId The ID of the last post the user has seen
     * @return Collection
     */
    public function getPostsNewerThan(User $user, $followedUserIds, int $lastPostId): Collection
    {
        // Extract user IDs if we received a collection of User models
        if ($followedUserIds instanceof Collection) {
            $userIds = $followedUserIds->pluck('id')->toArray();
        } else if (is_array($followedUserIds)) {
            $userIds = $followedUserIds;
        } else {
            // If it's something else (like a Support Collection), convert to array
            $userIds = collect($followedUserIds)->toArray();
        }

        // Log what we're doing for debugging
        Log::info('Getting posts newer than', [
            'user_id' => $user->id,
            'last_post_id' => $lastPostId,
            'followed_user_ids' => $userIds
        ]);

        // Query for posts newer than the last post ID
        $posts = Post::where('id', '>', $lastPostId)
            ->whereIn('user_id', $userIds)
            ->orderBy('id', 'desc')
            ->get();

        Log::info('Found posts', [
            'count' => $posts->count(),
            'post_ids' => $posts->pluck('id')->toArray()
        ]);

        return $posts;
    }
    public function update(Post $post, array $data): Post
    {
        if (!$post || !$post->id) {
            Log::error('Attempted to update a null or invalid post', ['post' => $post]);
            throw new \Exception('Invalid post for update');
        }
        return DB::transaction(function () use ($post, $data) {
            Log::info('Updating post', ['post_id' => $post->id, 'data' => $data]);

            $now = now();
            $scheduledFor = $data['scheduled_for'] ?? $post->scheduled_for;
            $expiresAt = $data['expires_at'] ?? $post->expires_at;

            // Validate expires_at
            if ($expiresAt) {
                $compareDate = $scheduledFor ?: $now;
                if ($expiresAt <= $compareDate) {
                    $expiresAt = null;
                }
            }

            $post->update([
                'content' => $data['content'] ?? $post->content,
                'status' => $data['status'] ?? $post->status,
                'moderation_note' => $data['moderation_note'] ?? $post->moderation_note,
                'scheduled_for' => $scheduledFor,
                'expires_at' => $expiresAt,
            ]);

            Log::info('Post updated', ['post_id' => $post->id]);

            if (isset($data['media']) && is_array($data['media'])) {
                Log::info('Processing media for post update', ['post_id' => $post->id, 'media_count' => count($data['media'])]);

                // Get IDs of media in the update payload (only real numeric IDs)
                $payloadMediaIds = collect($data['media'])
                    ->pluck('id')
                    ->filter(fn($id) => is_numeric($id) && intval($id) > 0)
                    ->map(fn($id) => intval($id))
                    ->all();
                $existingMedia = $post->media()->get();
                $existingMediaIds = $existingMedia->pluck('id')->all();
                Log::info('Payload media IDs', ['payloadMediaIds' => $payloadMediaIds]);
                Log::info('Existing media IDs', ['existingMediaIds' => $existingMediaIds]);

                // Delete media that are not in the payload
                foreach ($existingMedia as $existing) {
                    if (!in_array($existing->id, $payloadMediaIds)) {
                        Log::info('Deleting removed media', ['media_id' => $existing->id, 'url' => $existing->url]);
                        $existing->previews()->delete();
                        $existing->delete();
                    }
                }

                // Now process the payload
                foreach ($data['media'] as $mediaItem) {
                    if (isset($mediaItem['id']) && is_numeric($mediaItem['id']) && intval($mediaItem['id']) > 0) {
                        // Existing media: update previews if needed
                        $media = $post->media()->find(intval($mediaItem['id']));
                        if ($media) {
                            Log::info('Updating existing media', ['media_id' => $media->id, 'url' => $media->url]);
                            $media->save();
                            // Update previews
                            if (isset($mediaItem['previews']) && is_array($mediaItem['previews'])) {
                                $media->previews()->delete();
                                foreach ($mediaItem['previews'] as $previewUrl) {
                                    if (is_string($previewUrl)) {
                                        $preview = $media->previews()->create(['url' => $previewUrl]);
                                        Log::info('Preview saved for update', ['preview_id' => $preview->id, 'media_id' => $media->id]);
                                    }
                                }
                            }
                        } else {
                            Log::warning('Media id in payload not found in DB', ['media_id' => $mediaItem['id']]);
                        }
                    } else if (isset($mediaItem['url'])) {
                        // New media: create
                        $media = new Media([
                            'user_id' => $post->user_id,
                            'type' => $mediaItem['type'],
                            'url' => $mediaItem['url'],
                        ]);
                        $post->media()->save($media);
                        Log::info('Media saved for update', ['media_id' => $media->id, 'post_id' => $post->id, 'url' => $media->url]);
                        if (isset($mediaItem['previews']) && is_array($mediaItem['previews'])) {
                            foreach ($mediaItem['previews'] as $previewUrl) {
                                if (is_string($previewUrl)) {
                                    $preview = $media->previews()->create(['url' => $previewUrl]);
                                    Log::info('Preview saved for update', ['preview_id' => $preview->id, 'media_id' => $media->id]);
                                }
                            }
                        }
                    }
                }
            }

            Log::info('Post update completed', ['post_id' => $post->id]);

            return $post->fresh()->load('media.previews');
        });
    }

    public function delete(Post $post): bool
    {
        return DB::transaction(function () use ($post) {
            $post->media()->delete();
            $post->likes()->delete();
            $post->comments()->delete();
            $post->permissionSets()->delete();

            return $post->delete();
        });
    }

    public function findById(int $id): ?Post
    {
        return Post::with(['media.previews', 'permissionSets.permissions'])->find($id);
    }

    public function getPostsForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Post::where('user_id', $user->id)
            ->with(['media.previews', 'permissionSets.permissions'])
            ->latest()
            ->paginate($perPage);
    }

    public function getVisiblePosts(User $viewer, int $perPage = 15): LengthAwarePaginator
    {
        return Post::whereHas('permissionSets', function ($query) use ($viewer) {
            $query->where(function ($q) use ($viewer) {
                $q->whereHas('permissions', function ($p) {
                    $p->where('type', 'public');
                })
                    ->orWhere(function ($q) use ($viewer) {
                        $q->whereHas('permissions', function ($p) {
                            $p->where('type', 'subscribed_all_tiers');
                        })
                            ->whereHas('user.subscribers', function ($s) use ($viewer) {
                                $s->where('subscriber_id', $viewer->id);
                            });
                    })
                    ->orWhereHas('purchases', function ($pu) use ($viewer) {
                        $pu->where('user_id', $viewer->id);
                    });
            });
        })
            ->with(['media.previews', 'permissionSets.permissions', 'user'])
            ->latest()
            ->paginate($perPage);
    }

    public function addLike(Post $post, User $user): void
    {
        $post->likes()->firstOrCreate(['user_id' => $user->id]);
    }

    public function removeLike(Post $post, User $user): void
    {
        $post->likes()->where('user_id', $user->id)->delete();
    }

    public function getComments(Post $post): LengthAwarePaginator
    {
        return $post->comments()->with('user')->latest()->paginate(15);
    }

    public function addComment(Post $post, User $user, string $content): Comment
    {
        return $post->comments()->create([
            'user_id' => $user->id,
            'content' => $content
        ]);
    }

    /**
     * Pin a post
     */
    public function pinPost(Post $post): void
    {
        $post->pin();
    }

    /**
     * Unpin a post
     */
    public function unpinPost(Post $post): void
    {
        $post->unpin();
    }

    /**
     * Get posts for user with pinned posts first
     */
    public function getPostsForUserWithPinnedFirst(User $user, int $perPage = 15, bool $viewAsFollower = false): LengthAwarePaginator
    {
        return Post::where('user_id', $user->id)
            ->with(['media.previews', 'user', 'permissionSets.permissions'])
            ->orderBy('pinned', 'desc')
            ->orderBy('pinned_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getRandomPostsFromUsers(User $currentUser, $userIds, int $perPage = 15, string $status = 'published'): LengthAwarePaginator
    {
        // Get blocked and muted user IDs
        $blockedAndMutedUserIds = DB::table('list_members')
            ->join('list_types', 'list_members.list_type_id', '=', 'list_types.id')
            ->where('list_members.user_id', $currentUser->id)
            ->whereIn('list_types.name', ['Blocked Accounts', 'Muted Accounts'])
            ->pluck('list_members.member_id');

        $query = Post::whereIn('user_id', $userIds)
            ->whereNotIn('user_id', $blockedAndMutedUserIds)
            ->with(['media.previews', 'user', 'permissionSets.permissions'])
            ->inRandomOrder();

        // Add status filter if provided
        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get posts from users sorted by ID in descending order (newest first)
     *
     * @param User $currentUser The current user
     * @param array|Collection $userIds IDs of users to get posts from
     * @param int $perPage Number of posts per page
     * @param string $status Post status filter
     * @return LengthAwarePaginator
     */
    public function getLatestPostsFromUsers(User $currentUser, $userIds, int $perPage = 15, string $status = 'published'): LengthAwarePaginator
    {
        // Get blocked and muted user IDs
        $blockedAndMutedUserIds = DB::table('list_members')
            ->join('list_types', 'list_members.list_type_id', '=', 'list_types.id')
            ->where('list_members.user_id', $currentUser->id)
            ->whereIn('list_types.name', ['Blocked Accounts', 'Muted Accounts'])
            ->pluck('list_members.member_id');

        // Get only creator users from the followed users
        $creatorUserIds = User::whereIn('id', $userIds)
            ->where('role', 'creator')
            ->where('id', '!=', $currentUser->id) // Exclude current user here as well
            ->pluck('id');

        // Log removed for production

        $query = Post::whereIn('user_id', $creatorUserIds)
            ->whereNotIn('user_id', $blockedAndMutedUserIds)
            ->where('user_id', '!=', $currentUser->id) // Keep this as a double-check
            ->where('status', $status)
            ->with(['media.previews', 'user', 'stats', 'permissionSets.permissions'])
            ->orderBy('id', 'desc'); // Sort by ID in descending order (newest first)

        return $query->paginate($perPage);
    }

    /**
     * Get ALL posts from users without any permission or blocking filters
     * Frontend will handle permissions and UI locking
     *
     * @param Collection|array $userIds Collection of user IDs or array of user IDs
     * @param int $perPage Number of posts per page
     * @param string $status Status filter for posts
     * @return LengthAwarePaginator
     */
    public function getAllPostsFromUsers($userIds, int $perPage = 15, string $status = 'published'): LengthAwarePaginator
    {
        // Convert userIds to array if it's a Collection
        $userIdsArray = $userIds instanceof Collection ? $userIds->toArray() : $userIds;
        
        // Get only creator users from the provided users
        $creatorUserIds = User::whereIn('id', $userIdsArray)
            ->where('role', 'creator')
            ->pluck('id');

        $query = Post::whereIn('user_id', $creatorUserIds)
            ->where('status', $status)
            ->with(['media.previews', 'user', 'stats', 'permissionSets.permissions'])
            ->orderBy('id', 'desc'); // Sort by ID in descending order (newest first)

        $result = $query->paginate($perPage);

        return $result;
    }

    public function getImagePreviewPosts($limit = 10)
    {
        return Post::whereHas('media.previews', function ($query) {
                $query->where(function ($q) {
                    $q->where('url', 'like', '%.jpg')
                      ->orWhere('url', 'like', '%.jpeg')
                      ->orWhere('url', 'like', '%.png')
                      ->orWhere('url', 'like', '%.gif');
                });
            })
            ->with(['media.previews', 'user'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getAllImagePreviews($limit = 100)
    {
        // Get all image previews with their parent media and user
        return \App\Models\MediaPreview::whereHas('media', function ($query) {
                $query->where(function ($q) {
                    $q->where('url', 'like', '%.jpg')
                      ->orWhere('url', 'like', '%.jpeg')
                      ->orWhere('url', 'like', '%.png')
                      ->orWhere('url', 'like', '%.gif');
                });
            })
            ->with(['media.post.user'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get public preview posts for unauthenticated users (auth page)
     *
     * @param int $limit
     * @return Collection
     */
    public function getPublicPreviewPosts($limit = 10)
    {
        // Get random posts with media that have previews
        return Post::whereHas('media.previews')
            ->where('status', 'published')
            ->with(['media.previews', 'user'])
            ->inRandomOrder() // Randomize for variety
            ->limit($limit)
            ->get();
    }

    private function getFullUrl($path)
    {
        if (empty($path)) {
            return null;
        }

        return app(\App\Services\MediaStorageService::class)->url($path);
    }

    /**
     * Search posts by query
     *
     * @param string $query Search query
     * @param int $perPage Number of posts per page
     * @return LengthAwarePaginator
     */
    public function searchPosts(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return Post::with(['user', 'media.previews', 'hashtags', 'stats'])
            ->where('status', Post::STATUS_PUBLISHED)
            ->where(function ($q) use ($query) {
                $q->where('content', 'like', "%{$query}%")
                  ->orWhereHas('hashtags', function ($hq) use ($query) {
                      $hq->where('name', 'like', "%{$query}%");
                  });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
