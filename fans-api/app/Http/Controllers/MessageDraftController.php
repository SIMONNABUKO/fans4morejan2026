<?php

namespace App\Http\Controllers;

use App\Models\MessageDraft;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MessageDraftController extends Controller
{
    /**
     * Get all drafts for the authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');

            $query = MessageDraft::where('user_id', $user->id)
                ->orderBy('updated_at', 'desc');

            if ($search) {
                $query->search($search);
            }

            $drafts = $query->paginate($perPage);

            // Transform drafts to include summary data
            $drafts->getCollection()->transform(function ($draft) {
                return $draft->getSummary();
            });

            return response()->json([
                'success' => true,
                'data' => $drafts
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching drafts', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch drafts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific draft
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $draft = MessageDraft::where('user_id', $user->id)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $draft
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Draft not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Store a new draft
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject' => 'nullable|string|max:255',
                'content' => 'required|string',
                'recipients' => 'nullable|array',
                'media' => 'nullable|array',
                'delivery_settings' => 'nullable|array',
                'draft_name' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            $draft = MessageDraft::create([
                'user_id' => $user->id,
                'subject' => $request->input('subject'),
                'content' => $request->input('content'),
                'recipients' => $request->input('recipients'),
                'media' => $request->input('media'),
                'delivery_settings' => $request->input('delivery_settings'),
                'draft_name' => $request->input('draft_name')
            ]);

            Log::info('Draft created successfully', [
                'draft_id' => $draft->id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Draft saved successfully',
                'data' => [
                    'draft_id' => $draft->id,
                    'draft_name' => $draft->draft_name,
                    'created_at' => $draft->created_at
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating draft', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save draft',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a draft
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject' => 'nullable|string|max:255',
                'content' => 'required|string',
                'recipients' => 'nullable|array',
                'media' => 'nullable|array',
                'delivery_settings' => 'nullable|array',
                'draft_name' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            
            $draft = MessageDraft::where('user_id', $user->id)
                ->findOrFail($id);

            $draft->update([
                'subject' => $request->input('subject'),
                'content' => $request->input('content'),
                'recipients' => $request->input('recipients'),
                'media' => $request->input('media'),
                'delivery_settings' => $request->input('delivery_settings'),
                'draft_name' => $request->input('draft_name')
            ]);

            Log::info('Draft updated successfully', [
                'draft_id' => $draft->id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Draft updated successfully',
                'data' => [
                    'draft_id' => $draft->id,
                    'updated_at' => $draft->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating draft', [
                'error' => $e->getMessage(),
                'draft_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update draft',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a draft
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $draft = MessageDraft::where('user_id', $user->id)
                ->findOrFail($id);

            $draft->delete();

            Log::info('Draft deleted successfully', [
                'draft_id' => $id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Draft deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting draft', [
                'error' => $e->getMessage(),
                'draft_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete draft',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent drafts (for quick access)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function recent(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $limit = $request->input('limit', 5);

            $drafts = MessageDraft::where('user_id', $user->id)
                ->recent($limit)
                ->get()
                ->map(function ($draft) {
                    return $draft->getSummary();
                });

            return response()->json([
                'success' => true,
                'data' => $drafts
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent drafts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert a draft to message data format
     *
     * @param int $id
     * @return JsonResponse
     */
    public function toMessageData($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $draft = MessageDraft::where('user_id', $user->id)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $draft->toMessageData()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to convert draft',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Duplicate a draft
     *
     * @param int $id
     * @return JsonResponse
     */
    public function duplicate($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $originalDraft = MessageDraft::where('user_id', $user->id)
                ->findOrFail($id);

            $duplicatedDraft = MessageDraft::create([
                'user_id' => $user->id,
                'subject' => $originalDraft->subject,
                'content' => $originalDraft->content,
                'recipients' => $originalDraft->recipients,
                'media' => $originalDraft->media,
                'delivery_settings' => $originalDraft->delivery_settings,
                'draft_name' => ($originalDraft->draft_name ?? 'Draft') . ' (Copy)'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Draft duplicated successfully',
                'data' => [
                    'original_id' => $originalDraft->id,
                    'duplicate_id' => $duplicatedDraft->id,
                    'draft_name' => $duplicatedDraft->draft_name
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate draft',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
