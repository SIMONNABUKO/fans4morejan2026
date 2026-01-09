<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\MediaStorageService;

class MediaController extends Controller
{
    protected $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:102400', // 100MB max
            'type' => 'required|in:image,video',
            'post_id' => 'required|exists:posts,id'
        ]);

        try {
            $file = $request->file('file');
            $type = $request->input('type');
            $user = Auth::user();

            // Get user's watermark text (for future use)
            $watermarkText = $user->media_watermark ?? $user->username;

            // Process media (currently without watermark)
            $url = $this->mediaService->processMedia($file, $watermarkText, $type);

            // Create media record
            $media = Media::create([
                'user_id' => $user->id,
                'post_id' => $request->input('post_id'),
                'type' => $type,
                'url' => $url,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            return response()->json([
                'message' => 'Media uploaded successfully',
                'media' => $media
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading media: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error uploading media',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $media = Media::findOrFail($id);
            
            // Check if user owns the media
            if ($media->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Delete file from storage
            app(MediaStorageService::class)->delete($media->url);

            // Delete media record
            $media->delete();

            return response()->json(['message' => 'Media deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error deleting media: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error deleting media',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 
