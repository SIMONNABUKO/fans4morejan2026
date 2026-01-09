<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Services\MediaStorageService;

class PostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Post::with(['user', 'media', 'stats'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where('content', 'like', "%{$search}%");
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc');

        $posts = $query->paginate($request->per_page ?? 20);

        // Get post statistics
        $stats = [
            'total' => Post::count(),
            'published' => Post::where('status', Post::STATUS_PUBLISHED)->count(),
            'pending' => Post::where('status', Post::STATUS_PENDING)->count(),
            'rejected' => Post::where('status', Post::STATUS_REJECTED)->count()
        ];

        return response()->json([
            'data' => PostResource::collection($posts),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'stats' => $stats
            ]
        ]);
    }

    public function show($id): JsonResponse
    {
        $post = Post::with(['user', 'comments.user', 'media', 'stats'])
            ->findOrFail($id);
        
        return response()->json(new PostResource($post));
    }

    public function update(Request $request, $id): JsonResponse
    {
        $post = Post::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:published,pending,rejected',
            'moderation_note' => 'nullable|string|max:500'
        ]);

        $post->update($validated);

        // If post is rejected and there's a moderation note, create a notification
        if ($validated['status'] === 'rejected' && !empty($validated['moderation_note'])) {
            $post->user->notifications()->create([
                'type' => 'post_rejected',
                'data' => [
                    'post_id' => $post->id,
                    'reason' => $validated['moderation_note']
                ]
            ]);
        }

        return response()->json(new PostResource($post->fresh()));
    }

    public function destroy($id): JsonResponse
    {
        $post = Post::findOrFail($id);
        
        // Delete associated media files first
        foreach ($post->media as $media) {
            // Delete the actual files
            if ($media->url) {
                app(MediaStorageService::class)->delete($media->url);
            }
            foreach ($media->previews as $preview) {
                app(MediaStorageService::class)->delete($preview->url);
            }
        }
        
        $post->delete();
        
        return response()->json(['message' => 'Post deleted successfully']);
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'total' => Post::count(),
            'published' => Post::where('status', Post::STATUS_PUBLISHED)->count(),
            'pending' => Post::where('status', Post::STATUS_PENDING)->count(),
            'rejected' => Post::where('status', Post::STATUS_REJECTED)->count(),
            'today' => Post::whereDate('created_at', today())->count(),
            'this_week' => Post::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => Post::whereMonth('created_at', now()->month)->count()
        ];

        return response()->json($stats);
    }
} 
