<?php

namespace App\Http\Controllers;

use App\Models\MediaAlbum;
use App\Models\Media;
use App\Services\VaultService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class MediaAlbumController extends Controller
{
    protected $vaultService;

    public function __construct(VaultService $vaultService)
    {
        $this->vaultService = $vaultService;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $albums = $this->vaultService->getUserMediaAlbums($user);
        return response()->json([
            'status' => 'success',
            'data' => [
                'albums' => $albums
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $album = $this->vaultService->createCustomAlbum(
            $request->user(),
            $validated['name'],
            $validated['description'] ?? null
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'album' => $album
            ]
        ], 201);
    }

    public function show(MediaAlbum $mediaAlbum): JsonResponse
    {


        Log::info('Showing media album', [
            'album_id' => $mediaAlbum->id, 
            'name' => $mediaAlbum->name, 
            'direct_media_count' => $mediaAlbum->media_count
        ]);

        $contents = $this->vaultService->getAlbumContents($mediaAlbum);

        Log::info('Media album contents retrieved', [
            'album_id' => $mediaAlbum->id, 
            'content_count' => $contents->count(),
            'direct_media_count' => $mediaAlbum->media_count
        ]);

        try {
            $pivotCount = DB::table('media_album_items')->where('media_album_id', $mediaAlbum->id)->count();
            Log::info('Pivot table count', ['album_id' => $mediaAlbum->id, 'pivot_count' => $pivotCount]);
        } catch (QueryException $e) {
            Log::warning('media_album_items table does not exist', ['error' => $e->getMessage()]);
            $pivotCount = 0;
        }

        if ($contents->isEmpty()) {
            Log::warning('Empty media album contents', ['album_id' => $mediaAlbum->id]);
            return response()->json([
                'status' => 'success',
                'message' => 'Media album is empty',
                'data' => [
                    'album' => [
                        'id' => $mediaAlbum->id,
                        'name' => $mediaAlbum->name,
                        'description' => $mediaAlbum->description,
                        'is_default' => $mediaAlbum->is_default,
                        'created_at' => $mediaAlbum->created_at,
                        'updated_at' => $mediaAlbum->updated_at,
                    ],
                    'contents' => []
                ]
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'album' => [
                    'id' => $mediaAlbum->id,
                    'name' => $mediaAlbum->name,
                    'description' => $mediaAlbum->description,
                    'is_default' => $mediaAlbum->is_default,
                    'created_at' => $mediaAlbum->created_at,
                    'updated_at' => $mediaAlbum->updated_at,
                ],
                'contents' => $contents
            ]
        ]);
    }

    public function update(Request $request, MediaAlbum $album): JsonResponse
    {


        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $album->update($validated);

        return response()->json([
            'status' => 'success',
            'data' => [
                'album' => $album
            ]
        ]);
    }

    public function destroy(MediaAlbum $album): JsonResponse
    {

        $album->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Album deleted successfully'
        ], 200);
    }

    public function addMedia(Request $request, MediaAlbum $album): JsonResponse
    {


        $validated = $request->validate([
            'media_id' => 'required|exists:media,id',
        ]);

        $media = Media::findOrFail($validated['media_id']);
        $this->vaultService->addMediaToCustomAlbum($album, $media);

        return response()->json([
            'status' => 'success',
            'message' => 'Media added to album successfully'
        ]);
    }

    public function removeMedia(Request $request, MediaAlbum $album): JsonResponse
    {


        $validated = $request->validate([
            'media_id' => 'required|exists:media,id',
        ]);

        $media = Media::findOrFail($validated['media_id']);
        $this->vaultService->removeMediaFromAlbum($album, $media);

        return response()->json([
            'status' => 'success',
            'message' => 'Media removed from album successfully'
        ]);
    }
}
