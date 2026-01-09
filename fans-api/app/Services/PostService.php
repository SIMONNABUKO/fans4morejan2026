<?php

namespace App\Services;

use App\Contracts\PostRepositoryInterface;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\PostResource;
use App\Events\PostCreated;
use App\Events\PostUpdated;
use App\Events\PostDeleted;
use App\Services\StatsService;
use App\Services\HashtagService;
use App\Services\MediaService;
use App\Services\MediaStorageService;

class PostService
{
    protected $postRepository;
    protected $permissionService;
    protected $vaultService;
    protected $postTagService;
    protected $statsService;
    protected $hashtagService;
    protected $mediaService;
    protected $mediaStorage;

    public function __construct(
        PostRepositoryInterface $postRepository,
        PermissionService $permissionService,
        VaultService $vaultService,
        PostTagService $postTagService,
        StatsService $statsService,
        HashtagService $hashtagService,
        MediaService $mediaService,
        MediaStorageService $mediaStorage
    ) {
        $this->postRepository = $postRepository;
        $this->permissionService = $permissionService;
        $this->vaultService = $vaultService;
        $this->postTagService = $postTagService;
        $this->statsService = $statsService;
        $this->hashtagService = $hashtagService;
        $this->mediaService = $mediaService;
        $this->mediaStorage = $mediaStorage;
    }

    public function createPost(array $data, User $user): Post
    {
        Log::info('PostService::createPost START', ['user_id' => $user->id, 'data' => $data]);
        try {
            $loadedPost = DB::transaction(function () use ($data, $user) {
                try {
                    $processedData = $this->processMediaFiles($data);
                    Log::info('PostService::createPost processedData', ['processedData' => $processedData]);
                    if (!isset($processedData['status'])) {
                        $processedData['status'] = 'published';
                    }
                    if (isset($data['scheduled_for'])) {
                        $processedData['scheduled_for'] = $data['scheduled_for'];
                    }
                    if (isset($data['expires_at'])) {
                        $processedData['expires_at'] = $data['expires_at'];
                    }
                    $post = $this->postRepository->create($processedData, $user);
                    Log::info('PostService::createPost post created', ['post_id' => $post->id]);
                    if (isset($data['permissions'])) {
                        $this->permissionService->createPermissions($post, $data['permissions']);
                    }
                    if (isset($processedData['media']) && is_array($processedData['media'])) {
                        foreach ($post->media as $media) {
                            $this->vaultService->addMediaToAlbum($media, 'All', $user);
                            $this->vaultService->addMediaToAlbum($media, 'Posts', $user);
                        }
                    }
                    if (isset($data['tagged_users']) && is_array($data['tagged_users'])) {
                        $this->processTaggedUsers($post, $data['tagged_users']);
                    } else {
                        $usernames = $this->postTagService->extractUsernames($post->content);
                        if (!empty($usernames)) {
                            $taggedUsers = $this->postTagService->findUsersByUsernames($usernames);
                            if ($taggedUsers->count() > 0) {
                                $this->postTagService->tagUsers($post, $taggedUsers->pluck('id')->toArray());
                            }
                        }
                    }
                    $post = $this->postRepository->findById($post->id);
                    $loadedPost = $post->load('media.previews', 'user', 'stats');
                    $this->hashtagService->syncPostHashtags($loadedPost);
                    Log::info('PostService::createPost END', ['post_id' => $post->id]);
                    return $loadedPost;
                } catch (\Throwable $e) {
                    Log::error('PostService::createPost TRANSACTION ERROR', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            });
            Log::info('PostService::createPost TRANSACTION SUCCESS', ['user_id' => $user->id]);
            return $loadedPost;
        } catch (\Throwable $e) {
            Log::error('PostService::createPost OUTER ERROR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function updatePost(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data) {
            $processedData = $this->processMediaFiles($data);
            $updatedPost = $this->postRepository->update($post, $processedData);
            
            if (isset($data['permissions'])) {
                $this->permissionService->updatePermissions($post, $data['permissions']);
            }

            // Add new media to 'All' and 'Posts' albums
            if (isset($processedData['media']) && is_array($processedData['media'])) {
                foreach ($updatedPost->media()->where('created_at', '>=', $updatedPost->updated_at)->get() as $media) {
                    $this->vaultService->addMediaToAlbum($media, 'All', $post->user);
                    $this->vaultService->addMediaToAlbum($media, 'Posts', $post->user);
                }
            }
            
            // Process updated tags if present
            if (isset($data['tagged_users']) && is_array($data['tagged_users'])) {
                // Remove existing pending tags
                $post->tags()->where('status', 'pending')->delete();
                
                // Add new tags
                $this->processTaggedUsers($post, $data['tagged_users']);
            } else if (isset($data['content']) && $data['content'] !== $post->content) {
                // Content changed, check for new mentions
                $usernames = $this->postTagService->extractUsernames($data['content']);
                if (!empty($usernames)) {
                    $taggedUsers = $this->postTagService->findUsersByUsernames($usernames);
                    if ($taggedUsers->count() > 0) {
                        // Get existing tagged users
                        $existingTaggedUserIds = $post->taggedUsers()->pluck('users.id')->toArray();
                        
                        // Filter to only new tagged users
                        $newTaggedUsers = $taggedUsers->filter(function ($user) use ($existingTaggedUserIds) {
                            return !in_array($user->id, $existingTaggedUserIds);
                        });
                        
                        if ($newTaggedUsers->count() > 0) {
                            $this->postTagService->tagUsers($post, $newTaggedUsers->pluck('id')->toArray());
                        }
                    }
                }
            }

            // Pass scheduled_for and expires_at if present
            if (isset($data['scheduled_for'])) {
                $processedData['scheduled_for'] = $data['scheduled_for'];
            }
            if (isset($data['expires_at'])) {
                $processedData['expires_at'] = $data['expires_at'];
            }

            // Reload the post to ensure we have the latest data
            $post = $this->postRepository->findById($post->id);
            $loadedPost = $post->load('media.previews', 'user', 'stats');
            
            // Sync hashtags from post content
            $this->hashtagService->syncPostHashtags($loadedPost);
            
            // Broadcast a notification that a post was updated
            $notificationData = [
                'type' => 'post_updated',
                'post_id' => $loadedPost->id,
                'user_id' => $post->user_id,
                'username' => $post->user->username,
                'avatar' => $post->user->avatar,
                'timestamp' => now()->timestamp,
                'has_media' => $loadedPost->media->isNotEmpty()
            ];
            
            // Log the notification data for debugging
            Log::debug('Broadcasting post update notification:', $notificationData);
            
            // Broadcast the notification
            broadcast(new PostUpdated($notificationData));

            return $loadedPost;
        });
    }

    public function deletePost(Post $post): bool
    {
        // Delete associated media files
        foreach ($post->media as $media) {
            $this->deleteMediaFile($media->url);
            foreach ($media->previews as $preview) {
                $this->deleteMediaFile($preview->url);
            }
        }

        $postId = $post->id;
        $result = $this->postRepository->delete($post);
        
        // Broadcast the post deleted event
        if ($result) {
            broadcast(new PostDeleted($postId));
        }
        
        return $result;
    }

    public function getPost(int $id): ?Post
    {
        $post = $this->postRepository->findById($id)->load([
            'media.previews',
            'user',
            'stats',
            'permissionSets.permissions',
            'taggedUsers',
            'comments.user'
        ]);
        
        // Add permission information
        if ($post) {
            $currentUser = request()->user();
            if ($currentUser) {
                $post->user_has_permission = $this->permissionService->checkPermission($post, $currentUser);
                $post->required_permissions = $this->permissionService->getRequiredPermissions($post, $currentUser);
            } else {
                $post->user_has_permission = false;
                $post->required_permissions = [];
            }
        }
        
        return $post;
    }

    public function getUserPosts(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $posts = $this->postRepository->getPostsForUser($user, $perPage)->load('media.previews');
        
        // Add permission information
        $currentUser = request()->user();
        $userToCheck = $currentUser ?: $user; // Use the authenticated user if available, otherwise use the post owner
        
        $posts->getCollection()->transform(function ($post) use ($userToCheck) {
            $post->user_has_permission = $this->permissionService->checkPermission($post, $userToCheck);
            $post->required_permissions = $this->permissionService->getRequiredPermissions($post, $userToCheck);
            return $post;
        });
        
        return $posts;
    }

    public function getVisiblePosts(User $viewer, int $perPage = 15): LengthAwarePaginator
    {
        $posts = $this->postRepository->getVisiblePosts($viewer, $perPage)->load('media.previews');
        
        // Add permission information
        $posts->getCollection()->transform(function ($post) use ($viewer) {
            $post->user_has_permission = $this->permissionService->checkPermission($post, $viewer);
            $post->required_permissions = $this->permissionService->getRequiredPermissions($post, $viewer);
            return $post;
        });
        
        return $posts;
    }

    public function likePost(Post $post, User $user): void
    {
        $this->postRepository->addLike($post, $user);
    }

    public function unlikePost(Post $post, User $user): void
    {
        $this->postRepository->removeLike($post, $user);
    }

    public function getPostComments(Post $post): LengthAwarePaginator
    {
        return $this->postRepository->getComments($post);
    }

    public function addComment(Post $post, User $user, string $content): Comment
    {
        return $this->postRepository->addComment($post, $user, $content);
    }

    /**
     * Pin a post
     */
    public function pinPost(Post $post): void
    {
        $this->postRepository->pinPost($post);
    }

    /**
     * Unpin a post
     */
    public function unpinPost(Post $post): void
    {
        $this->postRepository->unpinPost($post);
    }

    /**
     * Get user posts with pinned posts first
     */
    public function getUserPostsWithPinnedFirst(User $user, int $perPage = 15, bool $viewAsFollower = false): LengthAwarePaginator
    {
        $posts = $this->postRepository->getPostsForUserWithPinnedFirst($user, $perPage, $viewAsFollower);
        
        // Add permission information
        $currentUser = Auth::user();
        $userToCheck = $currentUser ?: $user; // Use the authenticated user if available, otherwise use the post owner
        
        $posts->getCollection()->transform(function ($post) use ($userToCheck, $viewAsFollower) {
            // If viewing as follower, force user_has_permission to false
            if ($viewAsFollower) {
                $post->user_has_permission = false;
            } else {
                $post->user_has_permission = $this->permissionService->checkPermission($post, $userToCheck);
            }
            $post->required_permissions = $this->permissionService->getRequiredPermissions($post, $userToCheck);
            return $post;
        });
        
        // Load media relationships after transforming
        $posts->load('media.previews');
        
        return $posts;
    }
    
    /*
    public function getRandomPostsFromUsers(User $currentUser, $userIds, int $perPage = 15): LengthAwarePaginator
    {
        $posts = $this->postRepository->getRandomPostsFromUsers($currentUser, $userIds, $perPage);
        // Add permission information
        $posts->getCollection()->transform(function ($post) use ($currentUser) {
            $post->user_has_permission = $this->permissionService->checkPermission($post, $currentUser);
            $post->required_permissions = $this->permissionService->getRequiredPermissions($post, $currentUser);
            return $post;
        });
        return $posts;
    }
    */

    /**
     * Process tagged users for a post
     *
     * @param Post $post
     * @param array $taggedUserIds
     * @return void
     */
    private function processTaggedUsers(Post $post, array $taggedUserIds): void
    {
        if (empty($taggedUserIds)) {
            $post->status = 'published';
            $post->save();
            return;
        }

        // Set post status to pending if there are tags
        $post->status = 'pending';
        $post->save();

        // Tag users
        $this->postTagService->tagUsers($post, $taggedUserIds);
    }

    protected function processMediaFiles(array $data): array
    {
        if (isset($data['media']) && is_array($data['media'])) {
            foreach ($data['media'] as &$mediaItem) {
                // Only store if it's a new upload
                if (isset($mediaItem['file']) && $mediaItem['file'] instanceof UploadedFile) {
                    $mediaItem['url'] = $this->storeMedia($mediaItem['file']);
                    unset($mediaItem['file']);
                }
                // If url exists and is a string, leave as-is (do not re-store)
                // Handle previews
                if (isset($mediaItem['previewVersions']) && is_array($mediaItem['previewVersions'])) {
                    $mediaItem['previews'] = [];
                    foreach ($mediaItem['previewVersions'] as $previewFile) {
                        if ($previewFile instanceof UploadedFile) {
                            $mediaItem['previews'][] = $this->storeMedia($previewFile, 'previews');
                        } elseif (is_string($previewFile) && (str_starts_with($previewFile, 'http://') || str_starts_with($previewFile, 'https://'))) {
                            $mediaItem['previews'][] = $previewFile;
                        }
                    }
                    unset($mediaItem['previewVersions']);
                }
            }
        }
        return $data;
    }

    protected function storeMedia($fileOrUrl, string $directory = 'posts'): string
    {
        // If already a full URL, just return it
        if (is_string($fileOrUrl)) {
            // If it's already a full URL (starts with http), return as is
            if (str_starts_with($fileOrUrl, 'http://') || str_starts_with($fileOrUrl, 'https://')) {
                $path = $this->mediaStorage->path($fileOrUrl);
                return $path ?: $fileOrUrl;
            }
            
            // If it's a relative URL, make it absolute
            if (str_starts_with($fileOrUrl, '/storage/')) {
                return $this->mediaStorage->path($fileOrUrl) ?? ltrim($fileOrUrl, '/');
            }
        }

        // Handle UploadedFile
        if ($fileOrUrl instanceof UploadedFile) {
            $filename = $this->generateUniqueFilename($fileOrUrl);
            Log::info('📁 Storing post media file', [
                'filename' => $filename,
                'directory' => $directory
            ]);
            
            // Use MediaService to process the file with watermark support
            $type = str_starts_with($fileOrUrl->getMimeType(), 'image/') ? 'image' : 'video';
            
            // Get the authenticated user's watermark text
            $user = Auth::user();
            $watermarkText = null;
            if ($user) {
                if (!empty($user->media_watermark)) {
                    $watermarkText = $user->media_watermark;
                } else {
                    $watermarkText = 'https://fans4more.com/' . $user->username . '/posts';
                }
            }
            
            Log::info('🔍 Processing post media with watermark', [
                'type' => $type,
                'watermark_text' => $watermarkText,
                'user_id' => $user ? $user->id : null
            ]);
            
            // Use MediaService to process the file
            $path = $this->mediaService->processMedia($fileOrUrl, $watermarkText, $type);
            
            Log::info('✅ Post media processed with watermark', [
                'url' => $path,
                'watermark_applied' => !empty($watermarkText)
            ]);
            
            return $path;
        }

        // Fallback: return as string
        return (string) $fileOrUrl;
    }

    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->timestamp;
        $randomString = Str::random(8);
        
        return "{$originalName}_{$timestamp}_{$randomString}.{$extension}";
    }

    protected function deleteMediaFile(string $url): void
    {
        $this->mediaStorage->delete($url);
    }
}
