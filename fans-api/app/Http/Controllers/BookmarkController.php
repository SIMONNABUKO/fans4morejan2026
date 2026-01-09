<?php

namespace App\Http\Controllers;

use App\Services\BookmarkService;
use App\Services\PermissionService;
use App\Models\Post;
use App\Models\Media;
use App\Models\BookmarkAlbum;
use App\Models\Bookmark;
use App\Models\User;
use App\Http\Resources\BookmarkResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookmarkController extends Controller
{
    protected $bookmarkService;
    protected $permissionService;

    public function __construct(BookmarkService $bookmarkService, PermissionService $permissionService)
    {
        $this->bookmarkService = $bookmarkService;
        $this->permissionService = $permissionService;
    }

    public function index(Request $request): JsonResponse
    {
        $bookmarks = $this->bookmarkService->getUserBookmarks($request->user());
        
        // Apply permission checks to each bookmark
        $bookmarksWithPermissions = $this->applyPermissionChecks($bookmarks);
        
        return response()->json(BookmarkResource::collection($bookmarksWithPermissions));
    }

    public function bookmarkPost(Request $request, $postId): JsonResponse
    {
        Log::info('BookmarkPost controller called', [
            'post_id_param' => $postId,
            'user_id' => $request->user()->id,
            'route_parameters' => $request->route()->parameters(),
            'all_parameters' => $request->all(),
        ]);
        
        // Manually fetch the post
        $post = Post::findOrFail($postId);
        
        Log::info('Post fetched', [
            'post_id' => $post->id,
            'post_user_id' => $post->user_id,
        ]);
        
        $album = null;
        if ($request->has('album_id')) {
            $album = BookmarkAlbum::findOrFail($request->album_id);
        }
        $this->bookmarkService->bookmark($post, $request->user(), $album);
        return response()->json(['success' => true, 'message' => 'Post bookmarked successfully']);
    }

    public function unbookmarkPost($postId): JsonResponse
    {
        Log::info('UnbookmarkPost controller called', [
            'post_id_param' => $postId,
            'user_id' => auth()->id(),
            'route_parameters' => request()->route()->parameters(),
        ]);
        
        // Manually fetch the post
        $post = Post::findOrFail($postId);
        
        Log::info('Post fetched', [
            'post_id' => $post->id,
            'post_user_id' => $post->user_id,
        ]);
        
        $this->bookmarkService->unbookmark($post, auth()->user());
        return response()->json(['success' => true, 'message' => 'Post unbookmarked successfully']);
    }

    public function bookmarkMedia(Request $request, Media $media): JsonResponse
    {
        $album = null;
        if ($request->has('album_id')) {
            $album = BookmarkAlbum::findOrFail($request->album_id);
        }
        $this->bookmarkService->bookmark($media, $request->user(), $album);
        return response()->json(['message' => 'Media bookmarked successfully']);
    }

    public function unbookmarkMedia(Media $media): JsonResponse
    {
        $this->bookmarkService->unbookmark($media, auth()->user());
        return response()->json(['message' => 'Media unbookmarked successfully']);
    }

    public function createAlbum(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $album = $this->bookmarkService->createAlbum($request->user(), $validated['name'], $validated['description'] ?? null);
        return response()->json($album, 201);
    }

    public function updateAlbum(Request $request, BookmarkAlbum $album): JsonResponse
    {
        // Only allow if the album belongs to the authenticated user
        if ($album->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $updatedAlbum = $this->bookmarkService->updateAlbum($album, $validated['name'], $validated['description'] ?? null);
        return response()->json($updatedAlbum);
    }

    public function deleteAlbum(BookmarkAlbum $album): JsonResponse
    {
        // Only allow if the album belongs to the authenticated user
        if ($album->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->bookmarkService->deleteAlbum($album);
        return response()->json(null, 204);
    }

    public function getAlbumBookmarks($albumId): JsonResponse
    {
        $authUserId = auth()->id();

        // Find the album first
        $album = BookmarkAlbum::find($albumId);
        
        if (!$album) {
            Log::error('Bookmark album not found', [
                'album_id' => $albumId,
                'auth_user_id' => $authUserId,
            ]);
            return response()->json(['message' => 'Album not found'], 404);
        }

        // If the album doesn't belong to the current user, attempt to resolve a same-named album
        if ($album->user_id !== $authUserId) {
            Log::warning('Bookmark album ownership mismatch detected', [
                'requested_album_id' => $album->id,
                'requested_album_user_id' => $album->user_id,
                'auth_user_id' => $authUserId,
                'requested_album_name' => $album->name,
            ]);

            // Only auto-create if album has a valid name
            if (!$album->name) {
                Log::error('Cannot auto-create album: original album has no name', [
                    'album_id' => $album->id,
                    'auth_user_id' => $authUserId,
                ]);
                return response()->json(['message' => 'Cannot access this album'], 403);
            }

            // Try to find an album with the same name that belongs to the current user
            $ownedAlbum = BookmarkAlbum::where('user_id', $authUserId)
                ->where('name', $album->name)
                ->first();

            // Auto-create if missing
            if (!$ownedAlbum) {
                $ownedAlbum = BookmarkAlbum::create([
                    'user_id' => $authUserId,
                    'name' => $album->name,
                    'description' => $album->description,
                ]);

                Log::info('Auto-created missing bookmark album for user', [
                    'new_album_id' => $ownedAlbum->id,
                    'user_id' => $authUserId,
                    'name' => $ownedAlbum->name,
                ]);
            }

            // Use the resolved/created album for fetching bookmarks
            $album = $ownedAlbum;
        }

        try {
            $bookmarks = $this->bookmarkService->getAlbumBookmarks($album);
            
            // Apply permission checks to each bookmark
            $bookmarksWithPermissions = $this->applyPermissionChecks($bookmarks);
            
            return response()->json(BookmarkResource::collection($bookmarksWithPermissions));
        } catch (\Exception $e) {
            Log::error('Error fetching album bookmarks', [
                'album_id' => $album->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Failed to fetch album bookmarks'], 500);
        }
    }

    public function moveBookmark(Request $request, Bookmark $bookmark): JsonResponse
    {
        // Only allow if the bookmark belongs to the authenticated user
        if ($bookmark->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'album_id' => 'nullable|exists:bookmark_albums,id',
        ]);

        $album = null;
        if ($validated['album_id']) {
            $album = BookmarkAlbum::findOrFail($validated['album_id']);
        }

        $this->bookmarkService->moveBookmarkToAlbum($bookmark, $album);
        return response()->json(['message' => 'Bookmark moved successfully']);
    }

    /**
     * Get all albums for the authenticated user with accurate counts
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserAlbums(Request $request): JsonResponse
    {
        Log::info('Fetching user albums', [
            'user_id' => $request->user()->id
        ]);
        
        $albums = BookmarkAlbum::where('user_id', $request->user()->id)->get();
        
        // Enhance albums with accurate counts and thumbnails
        $enhancedAlbums = [];
        
        foreach ($albums as $album) {
            // Get actual count of bookmarks in this album
            $bookmarkCount = Bookmark::where('bookmark_album_id', $album->id)->count();
            
            Log::info('Album bookmark count', [
                'album_id' => $album->id,
                'name' => $album->name,
                'count' => $bookmarkCount
            ]);
            
            // Get a random bookmark for thumbnail
            $randomBookmark = $this->getRandomBookmarkWithMedia($album->id);
            $thumbnail = '';
            
            if ($randomBookmark) {
                $thumbnail = $this->getMediaUrlFromBookmark($randomBookmark);
            }
            
            $enhancedAlbums[] = [
                'id' => $album->id,
                'title' => $album->name,
                'description' => $album->description,
                'thumbnail' => $thumbnail,
                'count' => $bookmarkCount,
                'created_at' => $album->created_at,
                'updated_at' => $album->updated_at
            ];
        }
        
        Log::info('Returning enhanced albums', [
            'album_count' => count($enhancedAlbums)
        ]);
        
        return response()->json($enhancedAlbums);
    }

    /**
     * Get all albums for a specific user with accurate counts
     *
     * @param User $user
     * @return JsonResponse
     */
    public function getAlbumsByUser(User $user): JsonResponse
    {
        Log::info('Fetching albums for user', [
            'user_id' => $user->id,
            'requested_by' => auth()->id()
        ]);
        
        // Only return public albums for other users
        if (auth()->id() === $user->id) {
            $albums = BookmarkAlbum::where('user_id', $user->id)->get();
        } else {
            // For other users, return only the "Tagged" album or none
            $albums = BookmarkAlbum::where('user_id', $user->id)
                ->where('name', 'Tagged')
                ->get();
        }
        
        // Enhance albums with accurate counts and thumbnails
        $enhancedAlbums = [];
        
        foreach ($albums as $album) {
            // Get actual count of bookmarks in this album
            $bookmarkCount = Bookmark::where('bookmark_album_id', $album->id)->count();
            
            Log::info('Album bookmark count', [
                'album_id' => $album->id,
                'name' => $album->name,
                'count' => $bookmarkCount
            ]);
            
            // Get a random bookmark for thumbnail
            $randomBookmark = $this->getRandomBookmarkWithMedia($album->id);
            $thumbnail = '';
            
            if ($randomBookmark) {
                $thumbnail = $this->getMediaUrlFromBookmark($randomBookmark);
            }
            
            $enhancedAlbums[] = [
                'id' => $album->id,
                'title' => $album->name,
                'description' => $album->description,
                'thumbnail' => $thumbnail,
                'count' => $bookmarkCount,
                'created_at' => $album->created_at,
                'updated_at' => $album->updated_at
            ];
        }
        
        return response()->json($enhancedAlbums);
    }

    /**
     * Apply permission checks to a collection of bookmarks
     *
     * @param \Illuminate\Database\Eloquent\Collection $bookmarks
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function applyPermissionChecks($bookmarks)
    {
        return $bookmarks->map(function ($bookmark) {
            $bookmarkable = $bookmark->bookmarkable;
            
            // Skip permission checks for non-existent bookmarkables or previews
            if (!$bookmarkable || $bookmark->bookmarkable_type === 'App\\Models\\MediaPreview') {
                return $bookmark;
            }
            
            // For Media, check permissions via the parent model
            if ($bookmark->bookmarkable_type === 'App\\Models\\Media') {
                // Get the parent model (Post) that contains this media
                $parentModel = $this->getParentModelForMedia($bookmarkable);
                
                if ($parentModel) {
                    $bookmark->user_has_permission = $this->permissionService->checkPermission($parentModel, auth()->user());
                    $bookmark->required_permissions = $this->permissionService->getRequiredPermissions($parentModel, auth()->user());
                    
                    Log::info('Permission check for media in bookmark', [
                        'media_id' => $bookmarkable->id,
                        'parent_model' => get_class($parentModel),
                        'parent_id' => $parentModel->id,
                        'user_has_permission' => $bookmark->user_has_permission,
                        'required_permissions_count' => count($bookmark->required_permissions)
                    ]);
                } else {
                    // If no parent model found, default to having permission
                    $bookmark->user_has_permission = true;
                    $bookmark->required_permissions = [];
                    
                    Log::info('No parent model found for media in bookmark, defaulting to permission granted', [
                        'media_id' => $bookmarkable->id
                    ]);
                }
            }
            // For Post, check permissions directly
            elseif ($bookmark->bookmarkable_type === 'App\\Models\\Post') {
                $bookmark->user_has_permission = $this->permissionService->checkPermission($bookmarkable, auth()->user());
                $bookmark->required_permissions = $this->permissionService->getRequiredPermissions($bookmarkable, auth()->user());
                
                Log::info('Permission check for post in bookmark', [
                    'post_id' => $bookmarkable->id,
                    'user_has_permission' => $bookmark->user_has_permission,
                    'required_permissions_count' => count($bookmark->required_permissions)
                ]);
            }
            
            return $bookmark;
        });
    }

    /**
     * Get the parent model (Post) that contains a media item
     *
     * @param Media $media
     * @return mixed|null
     */
    private function getParentModelForMedia(Media $media)
    {
        // Get the mediable relationship
        if ($media->mediable_type === 'App\\Models\\Post') {
            return Post::find($media->mediable_id);
        } elseif ($media->mediable_type === 'App\\Models\\Message') {
            return \App\Models\Message::find($media->mediable_id);
        }
        
        return null;
    }

    /**
     * Get a random bookmark from an album that has media
     *
     * @param int $albumId
     * @return Bookmark|null
     */
    private function getRandomBookmarkWithMedia($albumId)
    {
        // First try to get a bookmark with Media type
        $mediaBookmark = Bookmark::where('bookmark_album_id', $albumId)
            ->where('bookmarkable_type', 'App\\Models\\Media')
            ->inRandomOrder()
            ->first();
            
        if ($mediaBookmark) {
            return $mediaBookmark;
        }
        
        // If no media bookmark, try to get a bookmark with Post type that has media
        $postBookmark = Bookmark::where('bookmark_album_id', $albumId)
            ->where('bookmarkable_type', 'App\\Models\\Post')
            ->inRandomOrder()
            ->first();
            
        if ($postBookmark) {
            // Check if the post has media
            $post = Post::with('media')->find($postBookmark->bookmarkable_id);
            if ($post && $post->media->isNotEmpty()) {
                return $postBookmark;
            }
        }
        
        // As a fallback, return any bookmark from the album
        return Bookmark::where('bookmark_album_id', $albumId)
            ->inRandomOrder()
            ->first();
    }

    /**
     * Get media URL from a bookmark
     *
     * @param Bookmark $bookmark
     * @return string
     */
    private function getMediaUrlFromBookmark($bookmark)
    {
        if (!$bookmark) {
            return '';
        }
        
        if ($bookmark->bookmarkable_type === 'App\\Models\\Media') {
            // If the bookmark is for a media item
            $media = Media::find($bookmark->bookmarkable_id);
            if ($media) {
                // Use the direct URL property instead of the accessor
                return $media->url;
            }
        } elseif ($bookmark->bookmarkable_type === 'App\\Models\\Post') {
            // If the bookmark is for a post, get the first media item
            $post = Post::with('media')->find($bookmark->bookmarkable_id);
            if ($post && $post->media->isNotEmpty()) {
                // Use the direct URL property instead of the accessor
                return $post->media->first()->url;
            }
        }
        
        return '';
    }
}