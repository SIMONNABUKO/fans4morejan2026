<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Http\Resources\HashtagResource;
use App\Services\HashtagService;
use App\Services\FeedService;
use App\Services\ContentVisibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    protected $hashtagService;
    protected $feedService;
    protected $contentVisibilityService;

    public function __construct(
        HashtagService $hashtagService, 
        FeedService $feedService,
        ContentVisibilityService $contentVisibilityService
    ) {
        $this->hashtagService = $hashtagService;
        $this->feedService = $feedService;
        $this->contentVisibilityService = $contentVisibilityService;
    }

    /**
     * Search posts by hashtag
     */
    public function searchByHashtag(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'hashtag' => 'required|string|max:50',
                'per_page' => 'integer|min:1|max:50'
            ]);

            $hashtag = $request->input('hashtag');
            $perPage = $request->input('per_page', 15);
            $user = $request->user();

            // Remove # if present
            $hashtag = ltrim($hashtag, '#');

            $posts = $this->hashtagService->searchPostsByHashtag($hashtag, $user, $perPage);

            return response()->json([
                'posts' => PostResource::collection($posts),
                'hashtag' => $hashtag,
                'total_posts' => $posts->total(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching posts by hashtag', [
                'error' => $e->getMessage(),
                'hashtag' => $request->input('hashtag')
            ]);

            return response()->json([
                'error' => 'Failed to search posts',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * General post search
     */
    public function searchPosts(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'query' => 'required|string|min:1|max:100',
                'per_page' => 'integer|min:1|max:50'
            ]);

            $query = $request->input('query');
            $perPage = $request->input('per_page', 15);
            $user = $request->user();

            // If query is just "#" or too short, return empty results
            if (trim($query) === '#' || strlen(trim($query)) < 2) {
                return response()->json([
                    'posts' => [],
                    'query' => $query,
                    'total_posts' => 0,
                    'current_page' => 1,
                    'last_page' => 1,
                ]);
            }

            // Check if query is a hashtag
            if (str_starts_with($query, '#')) {
                // Create a new request with hashtag parameter
                $hashtagRequest = new Request();
                $hashtagRequest->merge([
                    'hashtag' => $query,
                    'per_page' => $perPage
                ]);
                $hashtagRequest->setUserResolver($request->getUserResolver());
                
                return $this->searchByHashtag($hashtagRequest);
            }

            // General post search
            $posts = $this->feedService->searchPosts($query, $user, $perPage);

            return response()->json([
                'posts' => PostResource::collection($posts),
                'query' => $query,
                'total_posts' => $posts->total(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching posts', [
                'error' => $e->getMessage(),
                'query' => $request->input('query')
            ]);

            return response()->json([
                'error' => 'Failed to search posts',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search hashtags
     */
    public function searchHashtags(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'query' => 'required|string|min:1|max:50',
                'limit' => 'integer|min:1|max:20'
            ]);

            $query = $request->input('query');
            $limit = $request->input('limit', 10);

            // If query is just "#" or too short, return empty results
            if (trim($query) === '#' || strlen(trim($query)) < 2) {
                return response()->json([
                    'hashtags' => [],
                    'query' => $query,
                ]);
            }

            $hashtags = $this->hashtagService->searchHashtags($query, $limit);

            return response()->json([
                'hashtags' => HashtagResource::collection($hashtags),
                'query' => $query,
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching hashtags', [
                'error' => $e->getMessage(),
                'query' => $request->input('query')
            ]);

            return response()->json([
                'error' => 'Failed to search hashtags',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get popular hashtags
     */
    public function getPopularHashtags(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);
            $hashtags = $this->hashtagService->getPopularHashtags($limit);

            return response()->json([
                'hashtags' => HashtagResource::collection($hashtags),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting popular hashtags', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get popular hashtags',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trending hashtags
     */
    public function getTrendingHashtags(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);
            $hashtags = $this->hashtagService->getTrendingHashtags($limit);

            return response()->json([
                'hashtags' => HashtagResource::collection($hashtags),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting trending hashtags', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get trending hashtags',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get hashtag suggestions for post creation
     */
    public function getHashtagSuggestions(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'content' => 'required|string|max:1000',
                'limit' => 'integer|min:1|max:10'
            ]);

            $content = $request->input('content');
            $limit = $request->input('limit', 5);

            $suggestions = $this->hashtagService->getHashtagSuggestions($content, $limit);

            return response()->json([
                'suggestions' => HashtagResource::collection($suggestions),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting hashtag suggestions', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get hashtag suggestions',
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 