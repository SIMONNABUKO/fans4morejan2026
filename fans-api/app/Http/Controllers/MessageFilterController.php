<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Services\ListService;
use App\Services\MessageService;

class MessageFilterController extends Controller
{
    protected $listService;
    protected $messageService;

    public function __construct(ListService $listService, MessageService $messageService)
    {
        $this->listService = $listService;
        $this->messageService = $messageService;
    }

    /**
     * Get user's message filter preferences
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        try {
            // Get user's list-based filter preferences from settings
            $listFilters = $user->getSetting('message_filters', 'list_filters', []);
            $activeFilter = $user->getSetting('message_filters', 'active_filter', 'all');

            return response()->json([
                'list_filters' => $listFilters,
                'active_filter' => $activeFilter
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching message filter preferences', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to fetch message filter preferences'
            ], 500);
        }
    }

    /**
     * Update user's message filter preferences
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
            $user->setSetting('message_filters', 'list_filters', $validated['list_filters']);

            // Update active filter
            $user->setSetting('message_filters', 'active_filter', $validated['active_filter']);

            Log::info('Message filter preferences updated', [
                'user_id' => $user->id,
                'active_filter' => $validated['active_filter'],
                'list_filters_count' => count($validated['list_filters'])
            ]);

            return response()->json([
                'message' => 'Message filter preferences updated successfully',
                'list_filters' => $validated['list_filters'],
                'active_filter' => $validated['active_filter']
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating message filter preferences', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Failed to update message filter preferences',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get filtered conversations based on the specified filter
     */
    public function getFilteredConversations(Request $request): JsonResponse
    {
        $user = $request->user();
        $filterId = $request->input('filter_id', 'all');
        
        Log::info('🎯 FILTERED CONVERSATIONS REQUEST DEBUG', [
            'authenticated_user_id' => $user ? $user->id : null,
            'authenticated_user_name' => $user ? $user->name : null,
            'filter_id_received' => $filterId,
            'filter_id_type' => gettype($filterId),
            'request_params' => $request->all(),
        ]);
        
        try {
            Log::info('Filtered conversations request received', [
                'user_id' => $user->id,
                'filter_id' => $filterId
            ]);
            
            // Get conversations from MessageService
            $messageService = app(\App\Services\MessageService::class);
            $conversationsArray = $messageService->getRecentConversations($user);
            $conversations = collect($conversationsArray); // Convert array to Collection
            
            // Apply filtering based on filter ID
            if ($filterId !== 'all' && str_starts_with($filterId, 'list_')) {
                $listId = (int) str_replace('list_', '', $filterId);
                
                Log::info('Filtering conversations by list', [
                    'list_id' => $listId,
                    'filter_id' => $filterId
                ]);
                
                // Get list members
                $listService = app(\App\Services\ListService::class);
                $listMembers = $listService->getListMembers($user, $listId);
                
                if ($listMembers) {
                    $listMemberIds = $listMembers->pluck('id')->toArray();
                    
                    Log::info('🎯 LIST MEMBERS FOR CONVERSATIONS', [
                        'list_id' => $listId,
                        'member_ids' => $listMemberIds,
                        'member_names' => $listMembers->pluck('name')->toArray(),
                    ]);
                    
                    // Filter conversations to only include users in the list
                    $filteredConversations = $conversations->filter(function ($conversation) use ($listMemberIds) {
                        return in_array($conversation['user']['id'], $listMemberIds);
                    });
                    
                    $conversations = $filteredConversations;
                } else {
                    // No members in list, return empty
                    $conversations = collect([]);
                }
            }
            
            Log::info('🎯 FILTERED CONVERSATIONS RESULT', [
                'filter_id' => $filterId,
                'conversations_count' => $conversations->count(),
                'first_conversation_user' => $conversations->count() > 0 ? $conversations->first()['user']['name'] : null,
            ]);
            
            return response()->json([
                'conversations' => $conversations->values(), // Reset array keys
                'filter_id' => $filterId
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching filtered conversations', [
                'user_id' => $user->id,
                'filter_id' => $filterId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to fetch filtered conversations'
            ], 500);
        }
    }
} 