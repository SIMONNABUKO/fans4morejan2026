<?php

namespace App\Http\Controllers;

use App\Models\MassMessage;
use App\Models\ScheduledMessage;
use App\Models\User;
use App\Models\Lists;
use App\Services\MessageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MassMessageController extends Controller
{
    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    /**
     * Send a mass message immediately
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMassMessage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject' => 'nullable|string|max:255',
                'content' => 'required|string',
                'recipients' => 'required|array',
                'recipients.users' => 'sometimes|array',
                'recipients.lists' => 'sometimes|array',
                'media' => 'nullable|array',
                'mediaPermissions' => 'nullable|array',
                'permissions' => 'nullable|array',
                'options' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $recipientData = $request->input('recipients');
            
            // Get all recipient user IDs
            $recipientIds = $this->processRecipients($recipientData, $user->id);
            
            if (empty($recipientIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid recipients found'
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Get permissions from either 'mediaPermissions' or 'permissions' field
                $permissions = $request->input('mediaPermissions') ?? $request->input('permissions');

                // Create mass message campaign record
                $massMessage = MassMessage::create([
                    'sender_id' => $user->id,
                    'subject' => $request->input('subject'),
                    'content' => $request->input('content'),
                    'recipients_data' => $recipientData,
                    'media' => $request->input('media'),
                    'permissions' => $permissions,
                    'delivery_options' => $request->input('options', []),
                    'total_recipients' => count($recipientIds),
                    'status' => MassMessage::STATUS_SENDING,
                    'type' => MassMessage::TYPE_IMMEDIATE,
                    'started_at' => now()
                ]);

                Log::info('🔒 Mass message created with permissions', [
                    'mass_message_id' => $massMessage->id,
                    'has_media' => !empty($massMessage->media),
                    'has_permissions' => !empty($massMessage->permissions),
                    'permissions_structure' => $massMessage->permissions
                ]);

                // Start sending messages
                $this->sendToRecipients($massMessage, $recipientIds);

                DB::commit();

                Log::info('Mass message sent successfully', [
                    'mass_message_id' => $massMessage->id,
                    'sender_id' => $user->id,
                    'recipient_count' => count($recipientIds)
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Mass message sent successfully',
                    'data' => [
                        'message_id' => $massMessage->id,
                        'recipient_count' => count($recipientIds),
                        'status' => 'sent'
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error sending mass message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send mass message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Schedule a mass message
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function scheduleMessage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject' => 'nullable|string|max:255',
                'content' => 'required|string',
                'recipients' => 'required|array',
                'scheduled_for' => 'required|date|after:now',
                'timezone' => 'nullable|string|max:50',
                'recurring' => 'nullable|array',
                'recurring.type' => 'nullable|in:daily,weekly,monthly',
                'recurring.end_date' => 'nullable|date|after:scheduled_for',
                'media' => 'nullable|array',
                'options' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $recipientData = $request->input('recipients');
            
            // Get all recipient user IDs
            $recipientIds = $this->processRecipients($recipientData, $user->id);
            
            if (empty($recipientIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid recipients found'
                ], 400);
            }

            // Convert scheduled time to UTC
            $scheduledFor = Carbon::parse($request->input('scheduled_for'));
            $timezone = $request->input('timezone', 'UTC');
            
            if ($timezone !== 'UTC') {
                $scheduledFor = $scheduledFor->setTimezone($timezone)->utc();
            }

            DB::beginTransaction();

            try {
                // Create mass message campaign record
                $massMessage = MassMessage::create([
                    'sender_id' => $user->id,
                    'subject' => $request->input('subject'),
                    'content' => $request->input('content'),
                    'recipients_data' => $recipientData,
                    'media' => $request->input('media'),
                    'delivery_options' => $request->input('options', []),
                    'total_recipients' => count($recipientIds),
                    'status' => MassMessage::STATUS_DRAFT,
                    'type' => $request->input('recurring') ? MassMessage::TYPE_RECURRING : MassMessage::TYPE_SCHEDULED
                ]);

                // Create scheduled message record
                $scheduledData = [
                    'sender_id' => $user->id,
                    'subject' => $request->input('subject'),
                    'content' => $request->input('content'),
                    'recipients' => $recipientData,
                    'media' => $request->input('media'),
                    'scheduled_for' => $scheduledFor,
                    'timezone' => $request->input('timezone', 'UTC'),
                    'delivery_options' => $request->input('options', []),
                    'mass_message_id' => $massMessage->id,
                    'status' => ScheduledMessage::STATUS_PENDING
                ];

                // Add recurring settings if provided
                $recurring = $request->input('recurring');
                if ($recurring && isset($recurring['type'])) {
                    $scheduledData['recurring_type'] = $recurring['type'];
                    if (isset($recurring['end_date'])) {
                        $scheduledData['recurring_end_date'] = Carbon::parse($recurring['end_date']);
                    }
                }

                $scheduledMessage = ScheduledMessage::create($scheduledData);

                DB::commit();

                Log::info('Message scheduled successfully', [
                    'scheduled_message_id' => $scheduledMessage->id,
                    'mass_message_id' => $massMessage->id,
                    'sender_id' => $user->id,
                    'scheduled_for' => $scheduledFor,
                    'recipient_count' => count($recipientIds)
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Message scheduled successfully',
                    'data' => [
                        'scheduled_id' => $scheduledMessage->id,
                        'mass_message_id' => $massMessage->id,
                        'recipient_count' => count($recipientIds),
                        'scheduled_for' => $scheduledFor,
                        'timezone' => $request->input('timezone', 'UTC'),
                        'is_recurring' => !is_null($scheduledMessage->recurring_type)
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error scheduling message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload media files for messages
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadMedia(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'media' => 'required|array',
                'media.*' => 'file|mimes:jpeg,jpg,png,gif,mp4,mov,avi|max:20480' // 20MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $uploadedFiles = [];
            $files = $request->file('media');

            foreach ($files as $index => $file) {
                if ($file && $file->isValid()) {
                    // Store file in storage/app/public/messages directory
                    $path = $file->store('messages', 'public');
                    $url = asset('storage/' . $path);
                    
                    $uploadedFiles[] = [
                        'url' => $url,
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'type' => str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'video'
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Files uploaded successfully',
                'files' => $uploadedFiles
            ]);

        } catch (\Exception $e) {
            Log::error('Error uploading media files', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload media files',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get mass message analytics
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getAnalytics($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $massMessage = MassMessage::where('sender_id', $user->id)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $massMessage->getStatistics()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's mass messages
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 15);
            $status = $request->input('status');

            $query = MassMessage::where('sender_id', $user->id)
                ->orderBy('created_at', 'desc');

            if ($status) {
                $query->where('status', $status);
            }

            $massMessages = $query->paginate($perPage);

            // Transform the data
            $massMessages->getCollection()->transform(function ($message) {
                return $message->getSummary();
            });

            return response()->json([
                'success' => true,
                'data' => $massMessages
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get mass messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process recipients and return user IDs
     *
     * @param array $recipientData
     * @param int $senderId
     * @return array
     */
    private function processRecipients(array $recipientData, int $senderId): array
    {
        $userIds = [];

        // Process individual users
        if (isset($recipientData['users']) && is_array($recipientData['users'])) {
            foreach ($recipientData['users'] as $user) {
                if (isset($user['id'])) {
                    $userIds[] = $user['id'];
                }
            }
        }

        // Process lists
        if (isset($recipientData['lists']) && is_array($recipientData['lists'])) {
            foreach ($recipientData['lists'] as $list) {
                if (isset($list['uniqueId'])) {
                    // Get users from list
                    $listUsers = $this->getUsersFromList($list['uniqueId'], $senderId);
                    $userIds = array_merge($userIds, $listUsers);
                }
            }
        }

        // Remove excluded users if provided
        if (isset($recipientData['excludedUsers']) && is_array($recipientData['excludedUsers'])) {
            $userIds = array_diff($userIds, $recipientData['excludedUsers']);
        }

        // Remove duplicates and sender's own ID
        $userIds = array_unique($userIds);
        $userIds = array_filter($userIds, function($id) use ($senderId) {
            return $id != $senderId;
        });

        return array_values($userIds);
    }

    /**
     * Get users from a list
     *
     * @param string $listId
     * @param int $senderId
     * @return array
     */
    private function getUsersFromList(string $listId, int $senderId): array
    {
        try {
            // Find the list
            $list = Lists::where('unique_id', $listId)
                ->where('user_id', $senderId)
                ->first();

            if (!$list) {
                return [];
            }

            // Get list members
            $members = $list->members()->pluck('user_id')->toArray();
            
            return $members;
        } catch (\Exception $e) {
            Log::error('Error getting users from list', [
                'list_id' => $listId,
                'sender_id' => $senderId,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Send messages to recipients
     *
     * @param MassMessage $massMessage
     * @param array $recipientIds
     */
    private function sendToRecipients(MassMessage $massMessage, array $recipientIds): void
    {
        $sentCount = 0;
        $failedCount = 0;

        // Prepare message data in the correct format expected by MessageService
        $messageData = [
            'content' => $massMessage->content,
            'media' => $massMessage->media,
            'permissions' => $massMessage->permissions // Add permissions support
        ];

        Log::info('🚀 Sending mass message to recipients', [
            'mass_message_id' => $massMessage->id,
            'recipient_count' => count($recipientIds),
            'has_media' => !empty($messageData['media']),
            'has_permissions' => !empty($messageData['permissions'])
        ]);

        foreach ($recipientIds as $recipientId) {
            try {
                Log::info('📤 Sending individual message', [
                    'mass_message_id' => $massMessage->id,
                    'sender_id' => $massMessage->sender_id,
                    'recipient_id' => $recipientId,
                    'has_media' => !empty($messageData['media']),
                    'has_permissions' => !empty($messageData['permissions'])
                ]);

                // Send individual message using the correct MessageService signature
                $result = $this->messageService->sendMessage(
                    $massMessage->sender,  // User object
                    $recipientId,          // int receiverId
                    $messageData          // array data (content, media, permissions)
                );

                if ($result) {
                    $sentCount++;
                    Log::info('✅ Message sent successfully', [
                        'recipient_id' => $recipientId,
                        'message_id' => $result->id
                    ]);
                } else {
                    $failedCount++;
                    Log::warning('❌ Message send returned null', [
                        'recipient_id' => $recipientId
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('❌ Failed to send message to recipient', [
                    'mass_message_id' => $massMessage->id,
                    'recipient_id' => $recipientId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                $failedCount++;
            }
        }

        Log::info('📊 Mass message sending completed', [
            'mass_message_id' => $massMessage->id,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'total_recipients' => count($recipientIds)
        ]);

        // Update mass message statistics
        $massMessage->updateStats($sentCount, $sentCount, $failedCount);
    }
}
