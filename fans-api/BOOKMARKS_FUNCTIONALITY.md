# Bookmarks Functionality Analysis

## Overview
The bookmarks system allows users to save posts and media content to organized collections called "albums". This is essentially a social media collection feature where users can organize their saved content.

**Frontend Location:** `/media` (named "My Media" in the sidebar)

## Core Components

### 1. Database Models

#### Bookmark Model (`app/Models/Bookmark.php`)
- **Polymorphic Model**: Can bookmark posts, media, or media previews
- **Fields**: `user_id`, `bookmarkable_id`, `bookmarkable_type`, `bookmark_album_id`
- **Relationships**:
  - `user()` - Belongs to User
  - `bookmarkable()` - Polymorphic relationship to Post, Media, or MediaPreview
  - `album()` - Belongs to BookmarkAlbum

#### BookmarkAlbum Model (`app/Models/BookmarkAlbum.php`)
- **Fields**: `user_id`, `name`, `description`
- **Relationships**:
  - `user()` - Belongs to User
  - `bookmarks()` - Has many Bookmarks

#### Post & Media Models
Both have polymorphic relationship:
```php
public function bookmarks()
{
    return $this->morphMany(Bookmark::class, 'bookmarkable');
}
```

### 2. Backend Services

#### BookmarkService (`app/Services/BookmarkService.php`)

**Key Methods:**

1. **`bookmark(Model $bookmarkable, User $user, ?BookmarkAlbum $album = null)`**
   - Creates a bookmark for a post/media
   - If album is specified, adds to that album
   - If already bookmarked in the same album, does nothing
   - Increments bookmark count in stats

2. **`unbookmark(Model $bookmarkable, User $user, ?BookmarkAlbum $album = null)`**
   - Removes bookmark from specified album
   - If album is null, removes from all albums
   - Decrements bookmark count in stats

3. **`isBookmarked(Model $bookmarkable, User $user, ?BookmarkAlbum $album = null)`**
   - Checks if item is bookmarked by user
   - Optionally checks specific album

4. **`getUserBookmarks(User $user)`**
   - Returns all bookmarks for a user
   - Includes bookmarkable (post/media) and album data

5. **`createAlbum(User $user, string $name, ?string $description)`**
   - Creates a new bookmark album

6. **`updateAlbum(BookmarkAlbum $album, string $name, ?string $description)`**
   - Updates album name and description

7. **`deleteAlbum(BookmarkAlbum $album)`**
   - Deletes album and its bookmarks

8. **`getAlbumBookmarks(BookmarkAlbum $album)`**
   - Returns all bookmarks in an album

9. **`moveBookmarkToAlbum(Bookmark $bookmark, ?BookmarkAlbum $album)`**
   - Moves bookmark to a different album

### 3. API Endpoints

**Bookmark Routes** (`routes/api.php`):
```php
// Bookmark routes
Route::prefix('bookmarks')->group(function () {
    Route::get('/', [BookmarkController::class, 'index']);                    // Get all user bookmarks
    Route::post('/posts/{post}', [BookmarkController::class, 'bookmarkPost']); // Bookmark a post
    Route::delete('/posts/{post}', [BookmarkController::class, 'unbookmarkPost']); // Unbookmark a post
    Route::post('/media/{media}', [BookmarkController::class, 'bookmarkMedia']); // Bookmark media
    Route::delete('/media/{media}', [BookmarkController::class, 'unbookmarkMedia']); // Unbookmark media
    Route::post('/move/{bookmark}', [BookmarkController::class, 'moveBookmark']); // Move bookmark to album
});

// Bookmark Album routes
Route::prefix('bookmark-albums')->group(function () {
    Route::get('/', [BookmarkController::class, 'getUserAlbums']);           // Get user's albums
    Route::get('/user/{user}', [BookmarkController::class, 'getAlbumsByUser']); // Get albums by user
    Route::post('/', [BookmarkController::class, 'createAlbum']);              // Create album
    Route::put('/{album}', [BookmarkController::class, 'updateAlbum']);       // Update album
    Route::delete('/{album}', [BookmarkController::class, 'deleteAlbum']);    // Delete album
    Route::get('/{album}/bookmarks', [BookmarkController::class, 'getAlbumBookmarks']); // Get album bookmarks
});
```

### 4. Frontend Store (`fans/src/stores/bookmarkStore.js`)

**State:**
- `albums` - Array of user's bookmark albums
- `currentAlbum` - Currently selected album
- `albumBookmarks` - Bookmarks in current album
- `loading` - Loading state
- `error` - Error message

**Key Actions:**
- `fetchUserAlbums()` - Fetch all albums for current user
- `fetchAlbumsByUser(userId)` - Fetch albums for specific user
- `createAlbum(albumData)` - Create new album
- `updateAlbum(albumId, albumData)` - Update album
- `deleteAlbum(albumId)` - Delete album
- `fetchAlbumBookmarks(albumId)` - Fetch bookmarks in album
- `bookmarkPost(postId, albumId)` - Bookmark a post
- `unbookmarkPost(postId)` - Unbookmark a post
- `bookmarkMedia(mediaId, albumId)` - Bookmark media
- `unbookmarkMedia(mediaId)` - Unbookmark media

### 5. Frontend Views

#### MediaCollectionView (`fans/src/views/MediaCollectionView.vue`)
- Main view showing all bookmark albums
- Displays albums in a grid layout
- Shows album thumbnail, title, item count, and creation date
- Allows creating new albums
- Route: `/media`

#### AlbumView (`fans/src/views/AlbumView.vue`)
- Shows all bookmarks in a specific album
- Transforms bookmarks into media items for display
- Handles permission checks for bookmarked content
- Allows interacting with bookmarked media (like, bookmark, stats)
- Displays media previews if available

## How It Works

### Creating a Bookmark

1. **User clicks bookmark button** on a post or media
2. **Frontend calls** `bookmarkStore.bookmarkPost(postId)` or `bookmarkStore.bookmarkMedia(mediaId)`
3. **Store makes API request** to `POST /api/bookmarks/posts/{postId}` or `POST /api/bookmarks/media/{mediaId}`
4. **Backend BookmarkController** receives request
5. **BookmarkService.bookmark()** is called:
   - Checks if bookmark already exists in album
   - Creates new Bookmark record with polymorphic relationship
   - Updates stats (increments bookmark count)
6. **Response returned** to frontend
7. **UI updates** to show bookmark state

### Organizing Bookmarks

1. **User creates album** via `createAlbum()` modal
2. **Album created** with name and optional description
3. **When bookmarking**, user can optionally specify album_id
4. **Bookmarks are stored** with `bookmark_album_id` reference
5. **User can move bookmarks** between albums using `moveBookmarkToAlbum()`

### Viewing Bookmarks

1. **User navigates to** `/media` (My Media)
2. **MediaCollectionView** fetches all albums via `fetchUserAlbums()`
3. **Albums displayed** in grid with thumbnails and counts
4. **User clicks** on an album
5. **AlbumView** fetches album bookmarks via `fetchAlbumBookmarks(albumId)`
6. **Bookmarks transformed** into media items for display
7. **Permission checks** applied to determine if user can view content

## Key Features

### 1. Multiple Albums
- Users can create multiple bookmark albums to organize content
- Each bookmark belongs to one album (or none if album_id is null)

### 2. Polymorphic Support
- Can bookmark posts, media, or media previews
- Same system handles all types

### 3. Duplicate Prevention
- If bookmark already exists in an album, new bookmark is not created
- Users can bookmark same item to multiple albums by creating separate bookmarks

### 4. Stats Integration
- Bookmarking increments `total_bookmarks` in stats
- Unbookmarking decrements the count
- Stats are tracked per post/media

### 5. Permission Aware
- Bookmarks display with permission status
- Users can only see bookmarked content they have permission to view
- Unlock modals shown for locked content

## Database Schema

```sql
bookmarks:
- id
- user_id (foreign key to users)
- bookmarkable_id (polymorphic id)
- bookmarkable_type (polymorphic type: Post, Media, MediaPreview)
- bookmark_album_id (foreign key to bookmark_albums, nullable)
- created_at
- updated_at

bookmark_albums:
- id
- user_id (foreign key to users)
- name
- description (nullable)
- created_at
- updated_at
```

## Frontend Route Configuration

```javascript
{
  path: '/media',
  name: 'media-collection',
  component: () => import('@/views/MediaCollectionView.vue'),
  meta: { requiresAuth: true }
}
```

## Notes

- On the sidebar, this feature is labeled "My Media" which can be confusing since it's actually for bookmarks/collections
- The system supports moving bookmarks between albums
- Each bookmark can only exist once per album
- Permission checks ensure users can only view content they're allowed to see
- Stats are automatically maintained when bookmarks are created/deleted

