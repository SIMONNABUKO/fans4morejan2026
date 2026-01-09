<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Services\ListService;

class FeedFilterController extends Controller
{
    protected $listService;

    public function __construct(ListService $listService)
    {
        $this->listService = $listService;
    }

    /**
     * Get user's feed filter preferences
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        try {
            // Get user's list-based filter preferences from settings
            $listFilters = $user->getSetting('feed_filters', 'list_filters', []);
            $activeFilter = $user->getSetting('feed_filters', 'active_filter', 'all');

            return response()->json([
                'list_filters' => $listFilters,
                'active_filter' => $activeFilter
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching feed filter preferences', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to fetch feed filter preferences'
            ], 500);
        }
    }

    /**
     * Update user's feed filter preferences
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        
        try {
            $validated = $request->validate([
                'list_filters' => 'required|array',
                'list_filters.*.id' => 'required|string',
                'list_filters.*.label' => 'required|string',
                'list_filters.*.type' => 'required|string',
                'list_filters.*.list_id' => 'required|integer',
                'list_filters.*.enabled' => 'required|boolean',
                'list_filters.*.order' => 'required|integer|min:0',
                'active_filter' => 'required|string'
            ]);

            // Clean up list filters to remove duplicates
            $listFilters = $validated['list_filters'];
            $seenListIds = [];
            $cleanedListFilters = [];
            
            foreach ($listFilters as $filter) {
                if (isset($filter['list_id']) && !in_array($filter['list_id'], $seenListIds)) {
                    $seenListIds[] = $filter['list_id'];
                    $cleanedListFilters[] = $filter;
                }
            }
            
            $validated['list_filters'] = $cleanedListFilters;

            // Update list filters
            $user->setSetting('feed_filters', 'list_filters', $validated['list_filters']);

            // Update active filter
            $user->setSetting('feed_filters', 'active_filter', $validated['active_filter']);

            // Log removed for production

            return response()->json([
                'message' => 'Feed filter preferences updated successfully',
                'list_filters' => $validated['list_filters'],
                'active_filter' => $validated['active_filter']
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating feed filter preferences', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Failed to update feed filter preferences',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get feed posts filtered by the specified filter
     */
    public function getFilteredFeed(Request $request): JsonResponse
    {
        $user = $request->user();
        $filterId = $request->input('filter_id', 'all');
        
        try {
            // Get the appropriate feed based on filter
            $feedService = app(\App\Services\FeedService::class);
            
            if ($filterId === 'all') {
                $feedData = $feedService->getFeedPosts($user);
            } else if (str_starts_with($filterId, 'list_')) {
                // Handle list filters - get posts from users in the selected list
                $listId = (int) str_replace('list_', '', $filterId);
                $feedData = $feedService->getListFeedPosts($user, $listId);
            } else {
                // Default to all posts
                $feedData = $feedService->getFeedPosts($user);
            }

            $postsCollection = \App\Http\Resources\PostResource::collection($feedData['posts']);
            $usersCollection = \App\Http\Resources\UserResource::collection($feedData['suggested_users'] ?? []);
            
            return response()->json([
                'posts' => $postsCollection,
                'suggested_users' => $usersCollection
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching filtered feed', [
                'user_id' => $user->id,
                'filter_id' => $filterId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to fetch filtered feed'
            ], 500);
        }
    }
} 