<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendMessageRequest;
use App\Http\Resources\MessageResource;
use App\Http\Resources\UserResource;
use App\Services\MessageService;
use App\Services\EmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use App\Models\Tip;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Services\PaymentService;

class MessageController extends Controller
{
    protected $messageService;
    protected $emailService;

    public function __construct(MessageService $messageService, EmailService $emailService)
    {
        $this->messageService = $messageService;
        $this->emailService = $emailService;
    }

    /**
     * Get recent conversations for the authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getRecentConversations(Request $request): JsonResponse
    {
        try {
            Log::info('📋 Getting recent conversations for user', [
                'user_id' => auth()->id()
            ]);

            $limit = $request->input('limit', 10);
            $conversations = $this->messageService->getRecentConversations(auth()->user(), $limit);

            Log::info('✅ Retrieved recent conversations', [
                'count' => count($conversations)
            ]);

            return response()->json($conversations);
        } catch (\Exception $e) {
            Log::error('❌ Error in getRecentConversations: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to retrieve recent conversations'], 500);
        }
    }

    /**
     * Get or create a conversation with another user
     *
     * @param Request $request
     * @param int $userId
     * @return JsonResponse
     */
    public function getOrCreateConversation(Request $request, int $userId): JsonResponse
    {
        Log::info('🚦 ENTERED getOrCreateConversation controller', ['userId' => $userId]);
        try {
            Log::info('🔍 Getting or creating conversation', [
                'user_id' => auth()->id(),
                'other_user_id' => $userId
            ]);

            $conversation = $this->messageService->getOrCreateConversation(auth()->user(), $userId);

            // Transform and return the user and messages as a plain array for proper JSON serialization
            return response()->json([
                'user' => new UserResource($conversation['user']),
                'messages' => MessageResource::collection($conversation['messages']),
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Exception caught in getOrCreateConversation', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            Log::error('❌ Error in getOrCreateConversation: ' . $e->getMessage(), [
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to retrieve or create conversation'], 500);
        }
    }
    /**
     * Send a message with a tip
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMessageWithTip(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'receiver_id' => 'required|exists:users,id',
                'content' => 'nullable|string|max:1000',
                'amount' => 'required|numeric|min:1',
                'currency' => 'required|string|in:USD',
                'payment_method' => 'nullable|string|in:wallet,ccbill',
            ]);

            $receiverId = $validated['receiver_id'];

            Log::info('💰 Sending message with tip', [
                'sender_id' => auth()->id(),
                'receiver_id' => $receiverId,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'] ?? 'ccbill'
            ]);

            // Step 1: Create the message with visibility set to false
            $messageData = $request->all();
            $messageData['visible'] = false; // Set visibility to false initially

            $message = $this->messageService->sendMessage(
                auth()->user(),
                $receiverId,
                $messageData
            );

            // Load relationships for the response
            $message->load(['media.previews', 'sender', 'receiver']);

            // Step 2: Create the tip record
            $tip = Tip::create([
                'sender_id' => auth()->id(),
                'recipient_id' => $receiverId,
                'amount' => $validated['amount'],
                'tippable_type' => Message::class,
                'tippable_id' => $message->id,
            ]);

            // Step 3: Process the tip payment
            $paymentService = app(PaymentService::class);

            // Process the payment with polymorphic relationship
            $paymentResult = $paymentService->processPayment(
                auth()->user(),
                $validated['amount'],
                Transaction::TIP,
                null,
                $receiverId,
                [
                    'context' => 'message',
                    'payment_method' => $validated['payment_method'] ?? 'wallet',
                    'tracking_link_id' => session('tracking_link_id')
                ],
                Tip::class,
                $tip->id
            );

            if ($paymentResult['success']) {
                // If wallet payment was successful, update message visibility immediately
                if (($validated['payment_method'] ?? 'wallet') === 'wallet' && !$paymentResult['redirect_required']) {
                    $message->update(['visible' => true]);
                    Log::info('✅ Wallet payment successful, message visibility updated', [
                        'message_id' => $message->id,
                        'tip_id' => $tip->id
                    ]);
                }

                Log::info('✅ Message with tip sent successfully', [
                    'message_id' => $message->id,
                    'tip_id' => $tip->id,
                    'payment_status' => $paymentResult['redirect_required'] ? 'pending' : 'completed'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => new MessageResource($message),
                    'tip' => [
                        'id' => $tip->id,
                        'amount' => $tip->amount
                    ],
                    'payment' => [
                        'success' => true,
                        'redirect_required' => $paymentResult['redirect_required'] ?? false,
                        'redirect_url' => $paymentResult['redirect_url'] ?? null,
                        'transaction_id' => $paymentResult['transaction_id'] ?? null,
                        'transaction_status' => $paymentResult['transaction_status'] ?? 'pending'
                    ]
                ], 201);
            } else {
                // If payment failed, delete both message and tip
                DB::transaction(function () use ($message, $tip) {
                    $tip->delete();
                    $message->delete();
                });

                Log::error('❌ Payment failed, message and tip deleted', [
                    'message_id' => $message->id,
                    'tip_id' => $tip->id,
                    'error' => $paymentResult['error'] ?? 'Unknown error'
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Payment failed: ' . ($paymentResult['error'] ?? 'Unknown error')
                ], 422);
            }
        } catch (\Exception $e) {
            Log::error('❌ Error in sendMessageWithTip: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to send message with tip: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Send a message to another user
     *
     * @param SendMessageRequest $request
     * @param int|null $receiverId
     * @return JsonResponse
     */
    public function sendMessage(SendMessageRequest $request, ?int $receiverId = null): JsonResponse
    {
        try {
            $receiverId = $receiverId ?? $request->input('receiver_id');

            if (!$receiverId) {
                return response()->json(['error' => 'Receiver ID is required'], 422);
            }

            Log::info('📤 Sending message', [
                'sender_id' => auth()->id(),
                'receiver_id' => $receiverId,
                'has_content' => !empty($request->input('content')),
                'has_media' => !empty($request->file('media'))
            ]);

            $message = $this->messageService->sendMessage(
                auth()->user(),
                $receiverId,
                $request->all()
            );

            // Load relationships for the response
            $message->load(['media.previews', 'sender', 'receiver']);

            Log::info('✅ Message sent successfully', [
                'message_id' => $message->id
            ]);

            // Return the message as a resource
            return response()->json(new MessageResource($message), 201);
        } catch (\Exception $e) {
            Log::error('❌ Error in sendMessage: ' . $e->getMessage(), [
                'receiver_id' => $receiverId,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Check if this is a tipping requirement exception
            if (str_contains($e->getMessage(), 'tip is required')) {
                Log::info('💰 Tipping requirement detected, returning 422 response');
                return response()->json([
                    'success' => false,
                    'error' => 'tip_required',
                    'message' => $e->getMessage(),
                    'data' => [
                        'requires_tip' => true,
                        'receiver_id' => $receiverId
                    ]
                ], 422);
            }
            
            Log::info('❌ Non-tipping error, returning 500 response');
            return response()->json(['error' => 'Failed to send message: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mark a message as read
     *
     * @param Request $request
     * @param int $messageId
     * @return JsonResponse
     */
    public function markAsRead(Request $request, int $messageId): JsonResponse
    {
        try {
            $message = Message::findOrFail($messageId);

            // Check if the user is the receiver of the message
            if ($message->receiver_id !== auth()->id()) {
                Log::warning('⚠️ Unauthorized attempt to mark message as read', [
                    'message_id' => $messageId,
                    'user_id' => auth()->id(),
                    'receiver_id' => $message->receiver_id
                ]);
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            Log::info('👁️ Marking message as read', [
                'message_id' => $messageId,
                'user_id' => auth()->id()
            ]);

            $this->messageService->markAsRead($message);

            Log::info('✅ Message marked as read successfully', [
                'message_id' => $messageId
            ]);

            return response()->json([
                'success' => true,
                'message_id' => $messageId,
                'read_at' => $message->read_at->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error in markAsRead: ' . $e->getMessage(), [
                'message_id' => $messageId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'An error occurred while marking the message as read'], 500);
        }
    }

    /**
     * Unlock a message by purchasing it
     *
     * @param Request $request
     * @param int $messageId
     * @return JsonResponse
     */
    public function unlockMessage(Request $request, int $messageId): JsonResponse
    {
        try {
            $message = Message::findOrFail($messageId);

            // Check if message requires payment
            if (!$message->requiresPayment()) {
                return response()->json([
                    'success' => false,
                    'error' => 'This message does not require unlocking'
                ], 400);
            }

            // Check if message is already purchased
            if ($message->isPurchasedBy(auth()->user())) {
                return response()->json([
                    'success' => false,
                    'error' => 'Message is already unlocked'
                ], 400);
            }

            // Get the price from creator settings
            $price = $message->getPrice();
            if (!$price) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid message price'
                ], 400);
            }

            // Process the payment
            $paymentService = app(PaymentService::class);
            
            DB::beginTransaction();
            try {
                // Process the payment with polymorphic relationship
                $paymentResult = $paymentService->processPayment(
                    auth()->user(),
                    $price,
                    Transaction::MESSAGE_PURCHASE,
                    null,
                    $message->receiver_id,
                    [
                        'context' => 'message_unlock',
                        'payment_method' => $request->input('payment_method', 'wallet')
                    ],
                    Message::class,
                    $message->id
                );

                if ($paymentResult['success']) {
                    // If wallet payment was successful and no redirect required
                    if (($request->input('payment_method', 'wallet') === 'wallet') && !$paymentResult['redirect_required']) {
                        Log::info('✅ Message unlocked successfully using wallet', [
                            'message_id' => $message->id,
                            'user_id' => auth()->id()
                        ]);

                        DB::commit();

                        return response()->json([
                            'success' => true,
                            'message' => new MessageResource($message->fresh()),
                            'payment' => [
                                'success' => true,
                                'redirect_required' => false,
                                'transaction_id' => $paymentResult['transaction_id'],
                                'transaction_status' => 'completed'
                            ]
                        ]);
                    }

                    // For external payments that require redirect
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => new MessageResource($message),
                        'payment' => [
                            'success' => true,
                            'redirect_required' => true,
                            'redirect_url' => $paymentResult['redirect_url'],
                            'transaction_id' => $paymentResult['transaction_id'],
                            'transaction_status' => 'pending'
                        ]
                    ]);
                } else {
                    DB::rollBack();

                    Log::error('❌ Message unlock payment failed', [
                        'message_id' => $message->id,
                        'user_id' => auth()->id(),
                        'error' => $paymentResult['error'] ?? 'Unknown error'
                    ]);

                    return response()->json([
                        'success' => false,
                        'error' => 'Payment failed: ' . ($paymentResult['error'] ?? 'Unknown error')
                    ], 422);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('❌ Error in unlockMessage: ' . $e->getMessage(), [
                'message_id' => $messageId,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to unlock message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a message
     *
     * @param Request $request
     * @param int $messageId
     * @return JsonResponse
     */
    public function deleteMessage(Request $request, int $messageId): JsonResponse
    {
        try {
            $message = Message::findOrFail($messageId);

            // Check if the user is the sender of the message
            if ($message->sender_id !== auth()->id()) {
                Log::warning('⚠️ Unauthorized attempt to delete message', [
                    'message_id' => $messageId,
                    'user_id' => auth()->id(),
                    'sender_id' => $message->sender_id
                ]);
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            Log::info('🗑️ Deleting message', [
                'message_id' => $messageId,
                'user_id' => auth()->id()
            ]);

            $result = $this->messageService->deleteMessage($message);

            if ($result) {
                Log::info('✅ Message deleted successfully', [
                    'message_id' => $messageId
                ]);
                return response()->json(['success' => true]);
            } else {
                Log::warning('⚠️ Failed to delete message', [
                    'message_id' => $messageId
                ]);
                return response()->json(['error' => 'Failed to delete message'], 400);
            }
        } catch (\Exception $e) {
            Log::error('❌ Error in deleteMessage: ' . $e->getMessage(), [
                'message_id' => $messageId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'An error occurred while deleting the message'], 500);
        }
    }

    /**
     * Search for users to message
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchUsers(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query');

            if (!$query || strlen($query) < 2) {
                return response()->json([]);
            }

            Log::info('🔍 Searching for users', [
                'query' => $query,
                'user_id' => auth()->id()
            ]);

            $users = $this->messageService->searchUsers($query);

            Log::info('✅ User search completed', [
                'count' => $users->count()
            ]);

            return response()->json(UserResource::collection($users));
        } catch (\Exception $e) {
            Log::error('❌ Error in searchUsers: ' . $e->getMessage(), [
                'query' => $request->input('query'),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'An error occurred while searching for users'], 500);
        }
    }

    /**
     * Get unread messages count for the authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUnreadMessagesCount(Request $request): JsonResponse
    {
        try {
            Log::info('🔢 Getting unread messages count', [
                'user_id' => auth()->id()
            ]);

            $count = $this->messageService->getUnreadMessagesCount(auth()->user());

            Log::info('✅ Retrieved unread messages count', [
                'count' => $count
            ]);

            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            Log::error('❌ Error in getUnreadMessagesCount: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'An error occurred while getting unread messages count'], 500);
        }
    }
}
