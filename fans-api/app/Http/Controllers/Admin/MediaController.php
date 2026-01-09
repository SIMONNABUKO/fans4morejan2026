<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Services\MediaStorageService;

class MediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Media::with(['user', 'previews', 'stats'])
            ->when($request->type, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->date_range, function ($query, $dateRange) {
                if (!empty($dateRange['start'])) {
                    $query->whereDate('created_at', '>=', $dateRange['start']);
                }
                if (!empty($dateRange['end'])) {
                    $query->whereDate('created_at', '<=', $dateRange['end']);
                }
                return $query;
            })
            ->orderBy('created_at', 'desc');

        $media = $query->paginate($request->per_page ?? 20);

        // Get media statistics
        $stats = [
            'total' => Media::count(),
            'active' => Media::where('status', 'active')->count(),
            'pending' => Media::where('status', 'pending')->count(),
            'flagged' => Media::where('status', 'flagged')->count(),
            'removed' => Media::where('status', 'removed')->count()
        ];

        return response()->json([
            'data' => $media->items(),
            'meta' => [
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
                'per_page' => $media->perPage(),
                'total' => $media->total(),
                'stats' => $stats
            ]
        ]);
    }

    public function show($id): JsonResponse
    {
        $media = Media::with(['user', 'previews', 'stats'])
            ->findOrFail($id);
        
        return response()->json($media);
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $media = Media::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:active,pending,flagged,removed'
        ]);

        $media->update($validated);

        // If media is flagged or removed, notify the user
        if (in_array($validated['status'], ['flagged', 'removed'])) {
            $media->user->notifications()->create([
                'type' => 'media_' . $validated['status'],
                'data' => [
                    'media_id' => $media->id,
                    'media_type' => $media->type
                ]
            ]);
        }

        return response()->json($media->fresh());
    }

    public function destroy($id): JsonResponse
    {
        $media = Media::findOrFail($id);
        
        // Delete the actual files
        if ($media->url) {
            app(MediaStorageService::class)->delete($media->url);
        }
        
        // Delete preview files
        foreach ($media->previews as $preview) {
            app(MediaStorageService::class)->delete($preview->url);
        }
        
        $media->delete();
        
        return response()->json(['message' => 'Media deleted successfully']);
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'total' => Media::count(),
            'active' => Media::where('status', 'active')->count(),
            'pending' => Media::where('status', 'pending')->count(),
            'flagged' => Media::where('status', 'flagged')->count(),
            'removed' => Media::where('status', 'removed')->count(),
            'by_type' => [
                'image' => Media::where('type', 'image')->count(),
                'video' => Media::where('type', 'video')->count(),
                'audio' => Media::where('type', 'audio')->count()
            ],
            'today' => Media::whereDate('created_at', today())->count(),
            'this_week' => Media::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => Media::whereMonth('created_at', now()->month)->count()
        ];

        return response()->json($stats);
    }
} 
