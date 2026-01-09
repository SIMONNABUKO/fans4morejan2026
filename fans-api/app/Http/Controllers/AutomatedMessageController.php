<?php

namespace App\Http\Controllers;

use App\Services\AutomatedMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\AutomatedMessageRequest;

class AutomatedMessageController extends Controller
{
    protected $automatedMessageService;

    public function __construct(AutomatedMessageService $automatedMessageService)
    {
        $this->automatedMessageService = $automatedMessageService;
    }

    public function store(Request $request)
    {
        try {
            Log::info('Automated message creation started', ['request' => $request->all()]);

            $validatedData = $request->validate([
                'trigger' => 'required|string',
                'content' => 'required|string',
                'sent_delay' => 'nullable|integer|min:0',
                'cooldown' => 'nullable|integer|min:0',
                'permissions' => 'nullable|string',
                'media' => 'nullable|array',
                'media.*.file' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:102400',
                'media.*.type' => 'nullable|string',
                'media.*.previewVersions.*' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:102400',
            ]);

            Log::info('Validation passed', ['validated_data' => $validatedData]);

            $message = $this->automatedMessageService->createMessage($request->all());

            Log::info('Message created successfully', ['message_id' => $message->id]);

            try {
                // Load relationships separately with error handling
                $message->load('media', 'permissionSets.permissions');
                Log::info('Relationships loaded successfully');
                
                return response()->json($message, 201);
            } catch (\Exception $relationshipError) {
                Log::warning('Failed to load relationships, returning message without them', [
                    'error' => $relationshipError->getMessage(),
                    'message_id' => $message->id
                ]);
                
                // Return the message even if relationships fail to load
                return response()->json($message->fresh(), 201);
            }
        } catch (\Exception $e) {
            Log::error('Failed to create automated message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Failed to create automated message',
                'message' => $e->getMessage(),
                'debug' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    public function index()
    {
        try {
            $messages = $this->automatedMessageService->getMessages();
            return response()->json(['data' => $messages]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch automated messages',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'trigger' => 'required|string',
                'content' => 'required|string',
                'sent_delay' => 'nullable|integer|min:0',
                'cooldown' => 'nullable|integer|min:0',
                'permissions' => 'nullable|string',
                'media' => 'nullable|array',
                'media.*.file' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:102400',
                'media.*.type' => 'nullable|string',
                'media.*.previewVersions.*' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:102400',
            ]);

            // Get all request data, not just validated data, to include the file
            $data = $request->all();

            $message = $this->automatedMessageService->updateMessage($id, $data);
            
            try {
                // Load relationships with error handling
                $message->load('media', 'permissionSets.permissions');
                return response()->json($message);
            } catch (\Exception $relationshipError) {
                Log::warning('Failed to load relationships for updated message', [
                    'error' => $relationshipError->getMessage(),
                    'message_id' => $message->id
                ]);
                
                return response()->json($message->fresh());
            }
        } catch (\Exception $e) {
            Log::error('Failed to update automated message: ' . $e->getMessage(), [
                'id' => $id,
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Failed to update automated message'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->automatedMessageService->deleteMessage($id);
            return response()->json(['message' => 'Message deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to delete automated message: ' . $e->getMessage(), [
                'id' => $id
            ]);
            return response()->json(['error' => 'Failed to delete automated message'], 500);
        }
    }

    public function toggle($id)
    {
        try {
            $message = $this->automatedMessageService->toggleMessageStatus($id);
            return response()->json($message);
        } catch (\Exception $e) {
            Log::error('Failed to toggle automated message status: ' . $e->getMessage(), [
                'id' => $id
            ]);
            return response()->json(['error' => 'Failed to toggle message status'], 500);
        }
    }
}