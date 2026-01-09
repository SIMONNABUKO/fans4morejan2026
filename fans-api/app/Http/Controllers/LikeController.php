<?php

namespace App\Http\Controllers;

use App\Services\LikeService;
use App\Models\Post;
use App\Models\Media;
use App\Models\MediaPreview;
use Illuminate\Http\JsonResponse;

class LikeController extends Controller
{
    protected $likeService;

    public function __construct(LikeService $likeService)
    {
        $this->likeService = $likeService;
    }

    public function likePost($postId): JsonResponse
    {
        $post = Post::find($postId);
        if (!$post || !$post->id) {
            return response()->json(['success' => false, 'message' => 'Invalid post.'], 400);
        }
        $this->likeService->like($post, auth()->user());
        return response()->json(['success' => true, 'message' => 'Post liked successfully']);
    }

    public function unlikePost($postId): JsonResponse
    {
        $post = Post::find($postId);
        if (!$post || !$post->id) {
            return response()->json(['success' => false, 'message' => 'Invalid post.'], 400);
        }
        $this->likeService->unlike($post, auth()->user());
        return response()->json(['success' => true, 'message' => 'Post unliked successfully']);
    }

    public function likeMedia(Media $media): JsonResponse
    {
        $this->likeService->like($media, auth()->user());
        return response()->json(['success' => true, 'message' => 'Media liked successfully']);
    }

    public function unlikeMedia(Media $media): JsonResponse
    {
        $this->likeService->unlike($media, auth()->user());
        return response()->json(['success' => true, 'message' => 'Media unliked successfully']);
    }

    public function likeMediaPreview(MediaPreview $mediaPreview): JsonResponse
    {
        $this->likeService->like($mediaPreview, auth()->user());
        return response()->json(['success' => true, 'message' => 'Media preview liked successfully']);
    }

    public function unlikeMediaPreview(MediaPreview $mediaPreview): JsonResponse
    {
        $this->likeService->unlike($mediaPreview, auth()->user());
        return response()->json(['success' => true, 'message' => 'Media preview unliked successfully']);
    }
}

