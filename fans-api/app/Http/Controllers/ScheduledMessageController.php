<?php

namespace App\Http\Controllers;

use App\Models\ScheduledMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ScheduledMessageController extends Controller
{
    /**
     * Get all scheduled messages for the authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 15);
            $status = $request->input('status'); // pending, sent, cancelled, failed

            $query = ScheduledMessage::where('sender_id', $user->id)
                ->orderBy('scheduled_for', 'asc');

            if ($status) {
                $query->where('status', $status);
            }

            $scheduledMessages = $query->paginate($perPage);

            // Transform data to include additional info
            $scheduledMessages->getCollection()->transform(function ($message) {
                return [
                    'id' => $message->id,
                    'subject' => $message->subject,
                    'content_preview' => \Illuminate\Support\Str::limit($message->content, 100),
                    'recipient_count' => $message->getRecipientCount(),
                    'status' => $message->status,
                    'scheduled_for' => $message->scheduled_for,
                    'timezone' => $message->timezone,
                    'is_recurring' => $message->isRecurring(),
                    'recurring_type' => $message->recurring_type,
                    'recurring_end_date' => $message->recurring_end_date,
                    'is_overdue' => $message->isOverdue(),
                    'next_occurrence' => $message->getNextOccurrence(),
                    'created_at' => $message->created_at,
                    'sent_at' => $message->sent_at,
                    'failure_reason' => $message->failure_reason
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $scheduledMessages
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching scheduled messages', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch scheduled messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific scheduled message
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $scheduledMessage = ScheduledMessage::where('sender_id', $user->id)
                ->with('sender', 'massMessage')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $scheduledMessage->id,
                    'subject' => $scheduledMessage->subject,
                    'content' => $scheduledMessage->content,
                    'recipients' => $scheduledMessage->recipients,
                    'media' => $scheduledMessage->media,
                    'delivery_options' => $scheduledMessage->delivery_options,
                    'status' => $scheduledMessage->status,
                    'scheduling_info' => $scheduledMessage->getSchedulingInfo(),
                    'recipient_count' => $scheduledMessage->getRecipientCount(),
                    'is_overdue' => $scheduledMessage->isOverdue(),
                    'analytics' => $scheduledMessage->analytics,
                    'created_at' => $scheduledMessage->created_at,
                    'sent_at' => $scheduledMessage->sent_at,
                    'failure_reason' => $scheduledMessage->failure_reason,
                    'mass_message' => $scheduledMessage->massMessage ? [
                        'id' => $scheduledMessage->massMessage->id,
                        'status' => $scheduledMessage->massMessage->status,
                        'statistics' => $scheduledMessage->massMessage->getStatistics()
                    ] : null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Scheduled message not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update a scheduled message (only pending messages can be updated)
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
                'scheduled_for' => 'required|date|after:now',
                'timezone' => 'nullable|string|max:50',
                'recurring' => 'nullable|array',
                'recurring.type' => 'nullable|in:daily,weekly,monthly',
                'recurring.end_date' => 'nullable|date|after:scheduled_for',
                'media' => 'nullable|array',
                'delivery_options' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            
            $scheduledMessage = ScheduledMessage::where('sender_id', $user->id)
                ->where('status', ScheduledMessage::STATUS_PENDING)
                ->findOrFail($id);

            // Convert scheduled time to UTC if needed
            $scheduledFor = \Carbon\Carbon::parse($request->input('scheduled_for'));
            $timezone = $request->input('timezone', 'UTC');
            
            if ($timezone !== 'UTC') {
                $scheduledFor = $scheduledFor->setTimezone($timezone)->utc();
            }

            // Prepare update data
            $updateData = [
                'subject' => $request->input('subject'),
                'content' => $request->input('content'),
                'recipients' => $request->input('recipients', $scheduledMessage->recipients),
                'media' => $request->input('media'),
                'scheduled_for' => $scheduledFor,
                'timezone' => $timezone,
                'delivery_options' => $request->input('delivery_options')
            ];

            // Handle recurring settings
            $recurring = $request->input('recurring');
            if ($recurring && isset($recurring['type'])) {
                $updateData['recurring_type'] = $recurring['type'];
                $updateData['recurring_end_date'] = isset($recurring['end_date']) 
                    ? \Carbon\Carbon::parse($recurring['end_date']) 
                    : null;
            } else {
                $updateData['recurring_type'] = null;
                $updateData['recurring_end_date'] = null;
            }

            $scheduledMessage->update($updateData);

            Log::info('Scheduled message updated successfully', [
                'scheduled_message_id' => $scheduledMessage->id,
                'user_id' => $user->id,
                'new_scheduled_for' => $scheduledFor
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Scheduled message updated successfully',
                'data' => [
                    'scheduled_message_id' => $scheduledMessage->id,
                    'scheduled_for' => $scheduledFor,
                    'timezone' => $timezone,
                    'is_recurring' => !is_null($scheduledMessage->recurring_type),
                    'updated_at' => $scheduledMessage->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating scheduled message', [
                'error' => $e->getMessage(),
                'scheduled_message_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update scheduled message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a scheduled message
     *
     * @param int $id
     * @return JsonResponse
     */
    public function cancel($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $scheduledMessage = ScheduledMessage::where('sender_id', $user->id)
                ->where('status', ScheduledMessage::STATUS_PENDING)
                ->findOrFail($id);

            $scheduledMessage->update([
                'status' => ScheduledMessage::STATUS_CANCELLED
            ]);

            Log::info('Scheduled message cancelled successfully', [
                'scheduled_message_id' => $id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Scheduled message cancelled successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error cancelling scheduled message', [
                'error' => $e->getMessage(),
                'scheduled_message_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel scheduled message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a scheduled message (only cancelled or failed messages can be deleted)
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $scheduledMessage = ScheduledMessage::where('sender_id', $user->id)
                ->whereIn('status', [
                    ScheduledMessage::STATUS_CANCELLED,
                    ScheduledMessage::STATUS_FAILED,
                    ScheduledMessage::STATUS_SENT
                ])
                ->findOrFail($id);

            $scheduledMessage->delete();

            Log::info('Scheduled message deleted successfully', [
                'scheduled_message_id' => $id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Scheduled message deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting scheduled message', [
                'error' => $e->getMessage(),
                'scheduled_message_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete scheduled message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get upcoming scheduled messages
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upcoming(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $limit = $request->input('limit', 5);
            $hours = $request->input('hours', 24); // Next 24 hours by default

            $scheduledMessages = ScheduledMessage::where('sender_id', $user->id)
                ->where('status', ScheduledMessage::STATUS_PENDING)
                ->where('scheduled_for', '>=', now())
                ->where('scheduled_for', '<=', now()->addHours($hours))
                ->orderBy('scheduled_for', 'asc')
                ->limit($limit)
                ->get()
                ->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'subject' => $message->subject,
                        'content_preview' => \Illuminate\Support\Str::limit($message->content, 50),
                        'recipient_count' => $message->getRecipientCount(),
                        'scheduled_for' => $message->scheduled_for,
                        'timezone' => $message->timezone,
                        'is_recurring' => $message->isRecurring(),
                        'time_until_send' => $message->scheduled_for->diffForHumans()
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $scheduledMessages
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch upcoming messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get overdue scheduled messages
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function overdue(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 15);

            $overdueMessages = ScheduledMessage::where('sender_id', $user->id)
                ->where('status', ScheduledMessage::STATUS_PENDING)
                ->where('scheduled_for', '<', now()->subHours(1))
                ->orderBy('scheduled_for', 'desc')
                ->paginate($perPage);

            $overdueMessages->getCollection()->transform(function ($message) {
                return [
                    'id' => $message->id,
                    'subject' => $message->subject,
                    'content_preview' => \Illuminate\Support\Str::limit($message->content, 100),
                    'recipient_count' => $message->getRecipientCount(),
                    'scheduled_for' => $message->scheduled_for,
                    'timezone' => $message->timezone,
                    'is_recurring' => $message->isRecurring(),
                    'overdue_since' => $message->scheduled_for->diffForHumans(),
                    'created_at' => $message->created_at
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $overdueMessages
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch overdue messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get scheduled message statistics
     *
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        try {
            $user = Auth::user();

            $stats = [
                'total' => ScheduledMessage::where('sender_id', $user->id)->count(),
                'pending' => ScheduledMessage::where('sender_id', $user->id)
                    ->where('status', ScheduledMessage::STATUS_PENDING)->count(),
                'sent' => ScheduledMessage::where('sender_id', $user->id)
                    ->where('status', ScheduledMessage::STATUS_SENT)->count(),
                'cancelled' => ScheduledMessage::where('sender_id', $user->id)
                    ->where('status', ScheduledMessage::STATUS_CANCELLED)->count(),
                'failed' => ScheduledMessage::where('sender_id', $user->id)
                    ->where('status', ScheduledMessage::STATUS_FAILED)->count(),
                'overdue' => ScheduledMessage::where('sender_id', $user->id)
                    ->where('status', ScheduledMessage::STATUS_PENDING)
                    ->where('scheduled_for', '<', now()->subHours(1))->count(),
                'upcoming_24h' => ScheduledMessage::where('sender_id', $user->id)
                    ->where('status', ScheduledMessage::STATUS_PENDING)
                    ->where('scheduled_for', '>=', now())
                    ->where('scheduled_for', '<=', now()->addHours(24))->count(),
                'recurring' => ScheduledMessage::where('sender_id', $user->id)
                    ->whereNotNull('recurring_type')->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
