<?php

namespace App\Http\Controllers;

use App\Services\FeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Log;

class FeedController extends Controller
{
    protected $feedService;

    public function __construct(FeedService $feedService)
    {
        $this->feedService = $feedService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $feedData = $this->feedService->getFeedPosts($request->user(), $perPage, $request->ip());

            return response()->json([
                'posts' => PostResource::collection($feedData['posts']),
                'suggested_users' => UserResource::collection($feedData['suggested_users']),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in FeedController', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'An error occurred while fetching the feed.'], 500);
        }
    }

    /**
     * Get feed previews for the authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPreviews(Request $request)
    {
        try {
            $user = $request->user();
            $perPage = $request->input('per_page', 15);

            $feedData = $this->feedService->getFeedPreviews($user, $perPage);

            return response()->json([
                'posts' => PostResource::collection($feedData['posts']),
                'suggested_users' => UserResource::collection($feedData['suggested_users']),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching feed previews', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to load feed previews',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get new posts since the last post ID
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getNewPosts(Request $request): JsonResponse
    {
        try {
            $lastPostId = $request->input('last_post_id', 0);
            $user = $request->user();

            // Get posts newer than the last post ID
            $newPosts = $this->feedService->getNewPostsSince($user, $lastPostId);

            return response()->json([
                'posts' => PostResource::collection($newPosts),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching new posts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id,
                'last_post_id' => $request->input('last_post_id', 0)
            ]);

            return response()->json(['error' => 'An error occurred while fetching new posts.'], 500);
        }
    }

    public function getImagePreviews(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);

            $posts = $this->feedService->getImagePreviewPosts($limit);

            return response()->json([
                'image_previews' => PostResource::collection($posts),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching image preview posts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to load image preview posts',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllImagePreviews(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 100);
            $previews = $this->feedService->getAllImagePreviews($limit);
            return response()->json([
                'image_previews' => \App\Http\Resources\ImagePreviewResource::collection($previews),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching all image previews', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Failed to load image previews',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get public previews for unauthenticated users (auth page)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPublicPreviews(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);
            $posts = $this->feedService->getPublicPreviews($limit);

            return response()->json([
                'posts' => PostResource::collection($posts),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching public previews', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to load public previews',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
