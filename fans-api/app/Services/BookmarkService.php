<?php

namespace App\Services;

use App\Models\Bookmark;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\BookmarkAlbum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class BookmarkService
{
    protected $statsService;

    public function __construct(StatsService $statsService)
    {
        $this->statsService = $statsService;
    }
    /**
     * Bookmark an item, optionally to a specific album
     * If the item is already bookmarked to a different album, it will create a new bookmark
     * rather than moving the existing one
     *
     * @param Model $bookmarkable
     * @param User $user
     * @param BookmarkAlbum|null $album
     * @return void
     */
    public function bookmark(Model $bookmarkable, User $user, ?BookmarkAlbum $album = null): void
    {
        Log::info('Bookmarking item', [
            'model_type' => get_class($bookmarkable),
            'model_id' => $bookmarkable->id,
            'user_id' => $user->id,
            'album_id' => $album ? $album->id : null
        ]);

        // If no album specified, auto-create or find a default "Posts" album
        if (!$album) {
            $modelType = get_class($bookmarkable);
            $defaultAlbumName = $modelType === 'App\Models\Post' ? 'Posts' : 'Media';
            
            $album = $user->bookmarkAlbums()->where('name', $defaultAlbumName)->first();
            
            if (!$album) {
                Log::info('Auto-creating default album', [
                    'user_id' => $user->id,
                    'album_name' => $defaultAlbumName
                ]);
                
                $album = $user->bookmarkAlbums()->create([
                    'name' => $defaultAlbumName,
                    'description' => "Default {$defaultAlbumName} collection"
                ]);
                
                Log::info('Default album created', [
                    'album_id' => $album->id,
                    'user_id' => $user->id,
                    'album_name' => $defaultAlbumName
                ]);
            }
        }

        // Check if a bookmark already exists in the specified album
        $existingBookmark = $bookmarkable->bookmarks()
            ->where('user_id', $user->id)
            ->where('bookmark_album_id', $album->id)
            ->first();
            
        if ($existingBookmark) {
            Log::info('Bookmark already exists in this album', [
                'bookmark_id' => $existingBookmark->id,
                'album_id' => $album->id
            ]);
            
            return; // Bookmark already exists in this album, nothing to do
        }
        
        // Create a new bookmark for this album
        $bookmark = new Bookmark();
        $bookmark->user_id = $user->id;
        $bookmark->bookmark_album_id = $album->id;
        
        Log::info('Creating new bookmark', [
            'user_id' => $user->id,
            'album_id' => $bookmark->bookmark_album_id
        ]);
        
        $bookmarkable->bookmarks()->save($bookmark);
        
        // Increment bookmark count in stats
        $this->statsService->incrementBookmarks($bookmarkable);
        
        Log::info('Bookmark created', [
            'bookmark_id' => $bookmark->id,
            'model_type' => get_class($bookmarkable),
            'model_id' => $bookmarkable->id,
            'user_id' => $user->id,
            'album_id' => $bookmark->bookmark_album_id
        ]);
    }

    /**
     * Unbookmark an item from a specific album
     * If album is null, unbookmark from all albums
     *
     * @param Model $bookmarkable
     * @param User $user
     * @param BookmarkAlbum|null $album
     * @return void
     */
    public function unbookmark(Model $bookmarkable, User $user, ?BookmarkAlbum $album = null): void
    {
        Log::info('Unbookmarking item', [
            'model_type' => get_class($bookmarkable),
            'model_id' => $bookmarkable->id,
            'user_id' => $user->id,
            'album_id' => $album ? $album->id : 'all'
        ]);
        
        $query = $bookmarkable->bookmarks()->where('user_id', $user->id);
        
        // If album is specified, only delete from that album
        if ($album) {
            $query->where('bookmark_album_id', $album->id);
        }
        
        $deleted = $query->delete();
        
        // Decrement bookmark count in stats for each deleted bookmark
        if ($deleted > 0) {
            for ($i = 0; $i < $deleted; $i++) {
                $this->statsService->decrementBookmarks($bookmarkable);
            }
        }
        
        Log::info('Bookmark deleted', [
            'model_type' => get_class($bookmarkable),
            'model_id' => $bookmarkable->id,
            'user_id' => $user->id,
            'album_id' => $album ? $album->id : 'all',
            'deleted_count' => $deleted
        ]);
    }

    /**
     * Check if an item is bookmarked by a user
     * Optionally check if it's bookmarked to a specific album
     *
     * @param Model $bookmarkable
     * @param User $user
     * @param BookmarkAlbum|null $album
     * @return bool
     */
    public function isBookmarked(Model $bookmarkable, User $user, ?BookmarkAlbum $album = null): bool
    {
        $query = $bookmarkable->bookmarks()->where('user_id', $user->id);
        
        if ($album) {
            $query->where('bookmark_album_id', $album->id);
        }
        
        return $query->exists();
    }

    public function getUserBookmarks(User $user): Collection
    {
        return $user->bookmarks()->with('bookmarkable', 'album')->get();
    }

    public function createAlbum(User $user, string $name, ?string $description = null): BookmarkAlbum
    {
        Log::info('Creating bookmark album', [
            'user_id' => $user->id,
            'name' => $name
        ]);
        
        $album = $user->bookmarkAlbums()->create([
            'name' => $name,
            'description' => $description,
        ]);
        
        Log::info('Bookmark album created', [
            'album_id' => $album->id,
            'user_id' => $user->id,
            'name' => $name
        ]);
        
        return $album;
    }

    public function updateAlbum(BookmarkAlbum $album, string $name, ?string $description = null): BookmarkAlbum
    {
        $album->update([
            'name' => $name,
            'description' => $description,
        ]);

        return $album;
    }

    public function deleteAlbum(BookmarkAlbum $album): void
    {
        $album->delete();
    }

    public function getAlbumBookmarks(BookmarkAlbum $album): Collection
    {
        Log::info('Fetching album bookmarks', [
            'album_id' => $album->id,
            'user_id' => $album->user_id
        ]);
        
        $bookmarks = $album->bookmarks()->with('bookmarkable')->get();
        
        Log::info('Album bookmarks fetched', [
            'album_id' => $album->id,
            'bookmark_count' => $bookmarks->count()
        ]);
        
        return $bookmarks;
    }

    public function moveBookmarkToAlbum(Bookmark $bookmark, ?BookmarkAlbum $album = null): void
    {
        $bookmark->update(['bookmark_album_id' => $album ? $album->id : null]);
    }
}