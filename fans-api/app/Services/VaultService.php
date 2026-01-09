<?php

namespace App\Services;

use App\Models\User;
use App\Models\MediaAlbum;
use App\Models\Media;
use Illuminate\Support\Collection;

class VaultService
{
    private const DEFAULT_ALBUMS = ['All', 'Posts', 'Messages', 'Automated Messages'];
    public function createDefaultAlbums(User $user)
    {
        foreach (self::DEFAULT_ALBUMS as $albumName) {
            MediaAlbum::firstOrCreate(
                ['user_id' => $user->id, 'name' => $albumName],
                ['description' => "Default $albumName album", 'is_default' => true]
            );
        }
    }

    public function addMediaToAlbum(Media $media, string $albumName, User $user)
    {
        $album = MediaAlbum::firstOrCreate(
            ['user_id' => $user->id, 'name' => $albumName],
            ['description' => "Default $albumName album", 'is_default' => true]
        );

        $album->media()->syncWithoutDetaching([$media->id]);
    }

    public function createCustomAlbum(User $user, string $name, string $description = null)
    {
        return MediaAlbum::create([
            'user_id' => $user->id,
            'name' => $name,
            'description' => $description,
            'is_default' => false,
        ]);
    }

    public function addMediaToCustomAlbum(MediaAlbum $album, Media $media)
    {
        $album->media()->syncWithoutDetaching([$media->id]);
    }

    public function removeMediaFromAlbum(MediaAlbum $album, Media $media)
    {
        $album->media()->detach($media->id);
    }

    public function getAlbumContents(MediaAlbum $album)
    {
        return $album->media()
        ->with('previews')
        ->get()
        ->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => $media->url,
                'type' => $media->type,
                'created_at' => $media->created_at,
                'previews' => $media->previews->map(function ($preview) {
                    return [
                        'id' => $preview->id,
                        'url' => $preview->url,
                        'type' => $preview->type
                    ];
                })
            ];
        });
    }

    public function getUserMediaAlbums(User $user): Collection
    {
        $this->ensureDefaultAlbumsExist($user);

        return MediaAlbum::where('user_id', $user->id)
            ->withCount('media')
            ->with(['media' => function ($query) {
                $query->where('type', 'image')
                    ->inRandomOrder()
                    ->limit(1);
            }])
            ->orderBy('is_default', 'desc')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($album) {
                $thumbnail = $album->media->first();
                return [
                    'id' => $album->id,
                    'name' => $album->name,
                    'description' => $album->description,
                    'is_default' => $album->is_default,
                    'media_count' => $album->media_count,
                    'thumbnail' => $thumbnail ? [
                        'id' => $thumbnail->id,
                        'url' => $thumbnail->url,
                        'type' => $thumbnail->type,
                    ] : null,
                    'created_at' => $album->created_at,
                    'updated_at' => $album->updated_at,
                ];
            });
    }

    private function ensureDefaultAlbumsExist(User $user)
    {
        $existingDefaultAlbums = MediaAlbum::where('user_id', $user->id)
            ->where('is_default', true)
            ->pluck('name')
            ->toArray();

        $missingAlbums = array_diff(self::DEFAULT_ALBUMS, $existingDefaultAlbums);

        foreach ($missingAlbums as $albumName) {
            $this->createDefaultAlbum($user, $albumName);
        }
    }

    private function createDefaultAlbum(User $user, string $name)
    {
        MediaAlbum::create([
            'user_id' => $user->id,
            'name' => $name,
            'description' => "Default $name album",
            'is_default' => true,
        ]);
    }

    public function addMediaToAlbums(Media $media, array $albumNames, User $user)
    {
        foreach ($albumNames as $albumName) {
            $album = MediaAlbum::firstOrCreate(
                ['user_id' => $user->id, 'name' => $albumName],
                ['description' => "Default $albumName album", 'is_default' => true]
            );

            $album->media()->syncWithoutDetaching([$media->id]);
        }
    }

  
}

