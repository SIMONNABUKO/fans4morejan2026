<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostTag;
use App\Models\User;
use App\Models\BookmarkAlbum;
use App\Notifications\PostTagApproved;
use App\Notifications\PostTagRejected;
use App\Notifications\PostTagRequest;
use Illuminate\Support\Facades\Log;

class PostTagService
{
    protected $bookmarkService;

    public function __construct(BookmarkService $bookmarkService)
    {
        $this->bookmarkService = $bookmarkService;
    }

    /**
     * Tag users in a post
     *
     * @param Post $post
     * @param array $userIds
     * @return array
     */
    public function tagUsers(Post $post, array $userIds)
    {
        $result = [
            'success' => [],
            'failed' => []
        ];

        // Set post status to pending if there are tags
        if (count($userIds) > 0) {
            $post->status = Post::STATUS_PENDING;
            $post->save();

            Log::info('Post status set to pending due to tag requests', [
                'post_id' => $post->id,
                'tag_count' => count($userIds)
            ]);
        }

        foreach ($userIds as $userId) {
            try {
                $user = User::findOrFail($userId);

                // Skip if user is the post creator
                if ($user->id === $post->user_id) {
                    Log::info('Skipping tag for post creator', [
                        'user_id' => $userId,
                        'post_id' => $post->id
                    ]);
                    continue;
                }

                // Check if tag already exists
                $existingTag = PostTag::where('post_id', $post->id)
                    ->where('user_id', $userId)
                    ->first();

                if (!$existingTag) {
                    // Create tag
                    $tag = PostTag::create([
                        'post_id' => $post->id,
                        'user_id' => $userId,
                        'status' => PostTag::STATUS_PENDING
                    ]);

                    Log::info('Created new tag request', [
                        'tag_id' => $tag->id,
                        'user_id' => $userId,
                        'post_id' => $post->id
                    ]);

                    // Get the tagger (post creator)
                    $tagger = User::find($post->user_id);

                    // Send notification to tagged user with correct parameter order
                    // Fixed: Pass parameters in the correct order (tagger, post, tag)
                    $user->notify(new PostTagRequest($tagger, $post, $tag));

                    $result['success'][] = $userId;
                } else {
                    Log::info('Tag already exists', [
                        'tag_id' => $existingTag->id,
                        'user_id' => $userId,
                        'post_id' => $post->id,
                        'status' => $existingTag->status
                    ]);

                    $result['success'][] = $userId; // Already tagged
                }
            } catch (\Exception $e) {
                Log::error('Failed to tag user', [
                    'user_id' => $userId,
                    'post_id' => $post->id,
                    'error' => $e->getMessage()
                ]);

                $result['failed'][] = [
                    'user_id' => $userId,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $result;
    }

    /**
     * Approve a tag request
     *
     * @param PostTag $tag
     * @return bool
     */
    public function approveTag(PostTag $tag)
    {
        // Only update if the tag is in pending status
        if ($tag->status !== PostTag::STATUS_PENDING) {
            Log::warning('Attempted to approve a tag that is not pending', [
                'tag_id' => $tag->id,
                'current_status' => $tag->status
            ]);
            return false;
        }

        $tag->status = PostTag::STATUS_APPROVED;
        $tag->save();

        $post = $tag->post;
        $taggedUser = $tag->user;

        Log::info('Tag approved', [
            'tag_id' => $tag->id,
            'post_id' => $post->id,
            'user_id' => $taggedUser->id
        ]);

        // Create or get the "Tagged" album for the user
        $this->createOrUpdateTaggedAlbum($post, $taggedUser);

        // Notify post creator that tag was approved
        $post->user->notify(new PostTagApproved($post, $taggedUser));

        // Check if post is ready to be published
        $this->updatePostStatus($post);

        return true;
    }

    /**
     * Create or update the "Tagged" album for a user and add post media to it
     *
     * @param Post $post
     * @param User $user
     * @return void
     */
    private function createOrUpdateTaggedAlbum(Post $post, User $user)
    {
        // Only proceed if the post has media
        if ($post->media->isEmpty()) {
            Log::info('Post has no media, skipping Tagged album creation', [
                'post_id' => $post->id,
                'user_id' => $user->id
            ]);
            return;
        }

        try {
            // Find existing "Tagged" album or create a new one
            $taggedAlbum = BookmarkAlbum::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => 'Tagged'
                ],
                [
                    'description' => 'Posts you were tagged in'
                ]
            );

            Log::info('Tagged album found or created', [
                'album_id' => $taggedAlbum->id,
                'user_id' => $user->id,
                'is_new' => $taggedAlbum->wasRecentlyCreated
            ]);

            // Add each media item from the post to the user's bookmarks in the Tagged album
            foreach ($post->media as $media) {
                // Bookmark the media in the Tagged album
                $this->bookmarkService->bookmark($media, $user, $taggedAlbum);
                
                Log::info('Media added to Tagged album', [
                    'media_id' => $media->id,
                    'album_id' => $taggedAlbum->id,
                    'user_id' => $user->id
                ]);
                
                // Also add any media previews if they exist
                if (method_exists($media, 'previews') && $media->previews->isNotEmpty()) {
                    foreach ($media->previews as $preview) {
                        Log::info('Adding media preview to Tagged album', [
                            'preview_id' => $preview->id,
                            'album_id' => $taggedAlbum->id
                        ]);
                        
                        $this->bookmarkService->bookmark($preview, $user, $taggedAlbum);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to create or update Tagged album', [
                'user_id' => $user->id,
                'post_id' => $post->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Reject a tag request
     *
     * @param PostTag $tag
     * @return bool
     */
    public function rejectTag(PostTag $tag)
    {
        // Only update if the tag is in pending status
        if ($tag->status !== PostTag::STATUS_PENDING) {
            Log::warning('Attempted to reject a tag that is not pending', [
                'tag_id' => $tag->id,
                'current_status' => $tag->status
            ]);
            return false;
        }

        $tag->status = PostTag::STATUS_REJECTED;
        $tag->save();

        $post = $tag->post;
        $taggedUser = $tag->user;

        Log::info('Tag rejected', [
            'tag_id' => $tag->id,
            'post_id' => $post->id,
            'user_id' => $taggedUser->id
        ]);

        // Notify post creator that tag was rejected
        $post->user->notify(new PostTagRejected($post, $taggedUser));

        // Check if post is ready to be published
        $this->updatePostStatus($post);

        return true;
    }

    /**
     * Update post status based on tag approvals
     *
     * @param Post $post
     * @return void
     */
    private function updatePostStatus(Post $post)
    {
        // Only proceed if post is in pending status
        if ($post->status !== Post::STATUS_PENDING) {
            Log::info('Post status not updated - not in pending status', [
                'post_id' => $post->id,
                'current_status' => $post->status
            ]);
            return;
        }

        // Check if post is ready to be published (no pending tags)
        if ($post->isReadyToPublish()) {
            // Get counts of different tag statuses
            $approvedCount = $post->approvedTags()->count();
            $totalTags = $post->tags()->count();

            Log::info('Post ready to be published - all tags processed', [
                'post_id' => $post->id,
                'approved_tags' => $approvedCount,
                'total_tags' => $totalTags
            ]);

            // Only publish if at least one tag was approved or there were no tags
            if ($approvedCount > 0 || $totalTags === 0) {
                $post->status = Post::STATUS_PUBLISHED;
                $post->save();

                Log::info('Post published after all tags were processed', [
                    'post_id' => $post->id,
                    'approved_tags' => $approvedCount,
                    'total_tags' => $totalTags
                ]);
            } else {
                // All tags were rejected, handle accordingly
                // You might want to set a different status or notify the user
                Log::info('All tags were rejected for post', [
                    'post_id' => $post->id,
                    'total_tags' => $totalTags
                ]);

                // Option: Still publish the post without tags
                $post->status = Post::STATUS_PUBLISHED;
                $post->save();

                Log::info('Post published despite all tags being rejected', [
                    'post_id' => $post->id
                ]);

                // Alternative option (uncomment if needed):
                // $post->status = Post::STATUS_REJECTED;
                // $post->save();
                // Log::info('Post marked as rejected as all tags were rejected', [
                //     'post_id' => $post->id
                // ]);
            }
        } else {
            Log::info('Post remains pending as there are still pending tags', [
                'post_id' => $post->id,
                'pending_tags' => $post->pendingTags()->count()
            ]);
        }
    }

    /**
     * Extract usernames from post content
     *
     * @param string $content
     * @return array
     */
    public function extractUsernames(string $content)
    {
        preg_match_all('/@([a-zA-Z0-9_]+)/', $content, $matches);

        if (!empty($matches[1])) {
            return $matches[1];
        }

        return [];
    }

    /**
     * Find users by usernames
     *
     * @param array $usernames
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findUsersByUsernames(array $usernames)
    {
        return User::whereIn('username', $usernames)->get();
    }

    /**
     * Get users previously tagged by the current user
     *
     * @param int $userId The ID of the user who created the tags
     * @param int $limit Maximum number of users to return
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPreviouslyTaggedUsers(int $userId, int $limit = 10)
    {
        // Get post IDs where the current user is the creator
        $postIds = Post::where('user_id', $userId)->pluck('id');

        // Get user IDs who were tagged in those posts
        $taggedUserIds = PostTag::whereIn('post_id', $postIds)
            ->where('status', '!=', PostTag::STATUS_REJECTED) // Exclude rejected tags
            ->select('user_id')
            ->distinct()
            ->pluck('user_id');

        // Get the user objects
        $users = User::whereIn('id', $taggedUserIds)
            ->select('id', 'name', 'username', 'avatar')
            ->limit($limit)
            ->get();

        Log::info('Retrieved previously tagged users', [
            'tagger_id' => $userId,
            'count' => $users->count()
        ]);

        return $users;
    }
}