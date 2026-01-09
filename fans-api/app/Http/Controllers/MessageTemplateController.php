<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MessageTemplateController extends Controller
{
    /**
     * Get all templates available to the authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $type = $request->input('type', 'all'); // all, own, global, popular

            $query = MessageTemplate::availableFor($user->id);

            // Apply filters
            switch ($type) {
                case 'own':
                    $query = MessageTemplate::forUser($user->id);
                    break;
                case 'global':
                    $query = MessageTemplate::global();
                    break;
                case 'popular':
                    $query = MessageTemplate::popular();
                    break;
            }

            if ($search) {
                $query->search($search);
            }

            $query->orderBy('usage_count', 'desc')
                  ->orderBy('created_at', 'desc');

            $templates = $query->paginate($perPage);

            // Transform templates to include summary data
            $templates->getCollection()->transform(function ($template) {
                return $template->getSummary();
            });

            return response()->json([
                'success' => true,
                'data' => $templates
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching templates', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch templates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific template
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $template = MessageTemplate::availableFor($user->id)
                ->findOrFail($id);

            // Increment usage count when template is viewed
            $template->incrementUsage();

            return response()->json([
                'success' => true,
                'data' => $template
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Store a new template
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'subject' => 'nullable|string|max:255',
                'content' => 'required|string',
                'media' => 'nullable|array',
                'is_global' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            // Check if user can create global templates (admin only)
            $isGlobal = $request->input('is_global', false);
            if ($isGlobal && !$user->hasRole('admin')) {
                $isGlobal = false;
            }

            $template = MessageTemplate::create([
                'user_id' => $user->id,
                'name' => $request->input('name'),
                'subject' => $request->input('subject'),
                'content' => $request->input('content'),
                'media' => $request->input('media'),
                'is_global' => $isGlobal
            ]);

            Log::info('Template created successfully', [
                'template_id' => $template->id,
                'user_id' => $user->id,
                'is_global' => $isGlobal
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template saved successfully',
                'data' => [
                    'template_id' => $template->id,
                    'name' => $template->name,
                    'is_global' => $template->is_global,
                    'created_at' => $template->created_at
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating template', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a template
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'subject' => 'nullable|string|max:255',
                'content' => 'required|string',
                'media' => 'nullable|array',
                'is_global' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            
            $template = MessageTemplate::where('user_id', $user->id)
                ->findOrFail($id);

            // Check if user can update global status
            $isGlobal = $request->input('is_global', $template->is_global);
            if ($isGlobal && !$user->hasRole('admin')) {
                $isGlobal = $template->is_global;
            }

            $template->update([
                'name' => $request->input('name'),
                'subject' => $request->input('subject'),
                'content' => $request->input('content'),
                'media' => $request->input('media'),
                'is_global' => $isGlobal
            ]);

            Log::info('Template updated successfully', [
                'template_id' => $template->id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template updated successfully',
                'data' => [
                    'template_id' => $template->id,
                    'updated_at' => $template->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating template', [
                'error' => $e->getMessage(),
                'template_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a template
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $template = MessageTemplate::where('user_id', $user->id)
                ->findOrFail($id);

            $template->delete();

            Log::info('Template deleted successfully', [
                'template_id' => $id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting template', [
                'error' => $e->getMessage(),
                'template_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get popular templates
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function popular(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);

            $templates = MessageTemplate::popular($limit)
                ->get()
                ->map(function ($template) {
                    return $template->getSummary();
                });

            return response()->json([
                'success' => true,
                'data' => $templates
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch popular templates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert a template to message data format
     *
     * @param int $id
     * @return JsonResponse
     */
    public function toMessageData($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $template = MessageTemplate::availableFor($user->id)
                ->findOrFail($id);

            // Increment usage count when template is used
            $template->incrementUsage();

            return response()->json([
                'success' => true,
                'data' => $template->toMessageData()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to convert template',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Duplicate a template
     *
     * @param int $id
     * @return JsonResponse
     */
    public function duplicate($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $originalTemplate = MessageTemplate::availableFor($user->id)
                ->findOrFail($id);

            $duplicatedTemplate = MessageTemplate::create([
                'user_id' => $user->id,
                'name' => $originalTemplate->name . ' (Copy)',
                'subject' => $originalTemplate->subject,
                'content' => $originalTemplate->content,
                'media' => $originalTemplate->media,
                'is_global' => false // Duplicated templates are always private
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template duplicated successfully',
                'data' => [
                    'original_id' => $originalTemplate->id,
                    'duplicate_id' => $duplicatedTemplate->id,
                    'name' => $duplicatedTemplate->name
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's own templates
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function myTemplates(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 15);

            $templates = MessageTemplate::forUser($user->id)
                ->orderBy('updated_at', 'desc')
                ->paginate($perPage);

            $templates->getCollection()->transform(function ($template) {
                return $template->getSummary();
            });

            return response()->json([
                'success' => true,
                'data' => $templates
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch your templates',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
