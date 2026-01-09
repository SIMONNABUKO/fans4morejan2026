<?php

namespace App\Services;

use App\Contracts\MessageRepositoryInterface;
use App\Models\Message;
use App\Models\User;
use App\Models\Media;
use App\Models\PermissionSet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use App\Events\NewMessageEvent;
use App\Events\MessageReadEvent;
use App\Services\MediaService;
use App\Services\MediaStorageService;
use App\Models\Tip;

class MessageService
{
    protected $messageRepository;
    protected $vaultService;
    protected $permissionService;
    protected $mediaService;
    protected $mediaStorage;

    public function __construct(
        MessageRepositoryInterface $messageRepository,
        VaultService $vaultService,
        PermissionService $permissionService,
        MediaService $mediaService,
        MediaStorageService $mediaStorage
    ) {
        $this->messageRepository = $messageRepository;
        $this->vaultService = $vaultService;
        $this->permissionService = $permissionService;
        $this->mediaService = $mediaService;
        $this->mediaStorage = $mediaStorage;
    }

    public function sendMessage(User $sender, int $receiverId, array $data): Message
    {
        $receiver = User::findOrFail($receiverId);

        // Check if tipping is required for this message
        $this->checkTippingRequirement($sender, $receiver);

        return DB::transaction(function () use ($sender, $receiver, $data) {
            // Process media files similar to PostService
            $processedData = $this->processMediaFiles($data);

            $messageData = [
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'content' => $processedData['content'] ?? null,
            ];

       
            $message = $this->messageRepository->create($messageData);

            // ❗ Consume one unconsumed tip (if any) by attaching it to this message
            $unconsumedTip = Tip::where('sender_id', $sender->id)
                ->where('recipient_id', $receiver->id)
                ->where('tippable_type', 'message')
                ->whereNull('tippable_id')
                ->orderByDesc('created_at')
                ->first();
            if ($unconsumedTip) {
                $unconsumedTip->tippable_id = $message->id;
                $unconsumedTip->save();
                Log::info('💰 Tip consumed for message', [
                    'tip_id' => $unconsumedTip->id,
                    'message_id' => $message->id
                ]);
            }

            // Handle media attachments
            if (isset($processedData['media']) && is_array($processedData['media'])) {
                Log::info('🖼️ Processing ' . count($processedData['media']) . ' media attachments');
                
                foreach ($processedData['media'] as $mediaItem) {
                    $media = Media::create([
                        'user_id' => $sender->id,
                        'mediable_id' => $message->id,
                        'mediable_type' => Message::class,
                        'type' => $mediaItem['type'] ?? 'image',
                        'url' => $mediaItem['url'],
                    ]);
                    
             

                    // Create preview versions if they exist
                    if (isset($mediaItem['previews']) && is_array($mediaItem['previews'])) {
                        Log::info('🔍 Creating ' . count($mediaItem['previews']) . ' preview versions');
                        
                        foreach ($mediaItem['previews'] as $previewUrl) {
                            $media->previews()->create([
                                'url' => $previewUrl
                            ]);
                            Log::info('🔍 Preview created', ['media_id' => $media->id, 'preview_url' => $previewUrl]);
                        }
                    }

                    // Add media to 'All' and 'Messages' albums in Vault
                    $this->vaultService->addMediaToAlbum($media, 'All', $sender);
                    $this->vaultService->addMediaToAlbum($media, 'Messages', $sender);
                  
                }
            }

            // Handle permissions if they exist
            if (isset($data['permissions'])) {
                // Parse the permissions if it's a string (JSON)
                $permissions = $data['permissions'];
                if (is_string($permissions)) {
                    $permissions = json_decode($permissions, true);
                }

                // Ensure we have a valid array before proceeding
                if (is_array($permissions)) {

                    Log::info('🔑 Creating message permissions', ['message_id' => $message->id, 'permissions' => json_encode($permissions)]);
                  
                    $this->createMessagePermissions($message, $permissions);
                } else {
                  
                }
            }

            // Load relationships for the event
            $message->load('media.previews');
            
            // Broadcast the new message event
        
            event(new NewMessageEvent($message));

            return $message;
        });
    }

    public function markAsRead(Message $message): void
    {
        Log::info('👁️ Marking message as read', [
            'message_id' => $message->id,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id
        ]);
        
        $this->messageRepository->markAsRead($message);
        
        // Broadcast the message read event
        Log::info('📢 Broadcasting message read event');
        event(new MessageReadEvent($message));
    }

    public function unlockMessage(Message $message, User $user, array $permissions): bool
    {
        try {
            Log::info('🔓 Unlocking message', [
                'message_id' => $message->id,
                'user_id' => $user->id,
                'permissions' => json_encode($permissions)
            ]);
        
            // Check if the user is trying to unlock their own message
            if ($message->sender_id === $user->id) {
                Log::info('✅ User is the sender, automatically granted permission');
                return true; // Sender always has permission
            }
        
            // If the message has no media, the receiver always has permission
            if ($message->media->isEmpty()) {
                // Log removed for production
                return true;
            }
        
            // Process each permission
            foreach ($permissions as $permission) {
                $type = $permission['type'] ?? null;
                $value = $permission['value'] ?? null;
            
                Log::info('🔒 Processing permission', [
                    'type' => $type,
                    'value' => $value
                ]);
            
                // Handle different permission types
                switch ($type) {
                    case 'subscribed_all_tiers':
                        // Check if user is subscribed to the sender
                        // This is a placeholder - implement your subscription check logic
                        $isSubscribed = true; // Simulate success
                        Log::info('💰 Subscription check: ' . ($isSubscribed ? 'Subscribed' : 'Not subscribed'));
                        if ($isSubscribed) {
                            // Create a record of this permission being granted
                            $this->recordPermissionGrant($message, $user, $type, $value);
                            return true;
                        }
                        break;
                    
                    case 'add_price':
                        // Process payment
                        // This is a placeholder - implement your payment logic
                        $paymentSuccessful = true; // Simulate success
                        Log::info('💰 Payment processing: ' . ($paymentSuccessful ? 'Successful' : 'Failed'));
                        if ($paymentSuccessful) {
                            // Create a record of this permission being granted
                            $this->recordPermissionGrant($message, $user, $type, $value);
                            return true;
                        }
                        break;
                    
                    case 'following':
                        // Check if user is following the sender
                        // This is a placeholder - implement your following check logic
                        $isFollowing = true; // Simulate success
                        // Log removed for production
                        if ($isFollowing) {
                            // Create a record of this permission being granted
                            $this->recordPermissionGrant($message, $user, $type, $value);
                            return true;
                        }
                        break;
                    
                    case 'limited_time':
                        // Process time-limited access
                        // This is a placeholder - implement your time-limited access logic
                        // Log removed for production
                        // Create a record of this permission being granted with expiration
                        $this->recordPermissionGrant($message, $user, $type, $value);
                        return true; // Simulate success
                    
                    default:
                        Log::warning('⚠️ Unknown permission type: ' . $type);
                        break;
                }
            }
        
            Log::warning('❌ No permission satisfied for unlocking message');
            return false; // No permission satisfied
        } catch (\Exception $e) {
            Log::error('❌ Error in unlockMessage: ' . $e->getMessage(), [
                'message_id' => $message->id,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Record that a permission has been granted to a user for a message
     * 
     * @param Message $message
     * @param User $user
     * @param string $type
     * @param mixed $value
     * @return void
     */
    protected function recordPermissionGrant(Message $message, User $user, string $type, $value): void
    {
        try {
            // Log removed for production
        } catch (\Exception $e) {
            Log::error('❌ Error recording permission grant: ' . $e->getMessage(), [
                'message_id' => $message->id,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function getOrCreateConversation(User $user1, int $user2Id): array
    {
        $user2 = User::findOrFail($user2Id);

        // Get messages between the two users with eager loading
        $messages = Message::where(function ($query) use ($user1, $user2Id) {
            $query->where('sender_id', $user1->id)
                ->where('receiver_id', $user2Id);
        })
            ->orWhere(function ($query) use ($user1, $user2Id) {
                $query->where('sender_id', $user2Id)
                    ->where('receiver_id', $user1->id);
            })
            ->with(['media.previews', 'sender', 'receiver', 'permissionSets.permissions'])
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Log removed for production

        // Transform the messages to add user_has_permission flag
        $messages->transform(function ($message) use ($user1) {
            // If user is the sender, they always have permission
            if ($message->sender_id === $user1->id) {
                $message->user_has_permission = true;
                // Log removed for production
            } else {
                // If the message has no media, the receiver always has permission
                if ($message->media->isEmpty()) {
                    $message->user_has_permission = true;
                    // Log removed for production
                } else {
                    // Check if the user has permission for this message
                    $message->user_has_permission = $this->permissionService->checkPermission($message, $user1);
                    // Log removed for production

                    // If receiver doesn't have permission, get the required permissions
                    if (!$message->user_has_permission) {
                        $message->required_permissions = $this->permissionService->getRequiredPermissions($message, $user1);
                        // Log removed for production
                    }
                }
            }

            return $message; // Make sure this return statement is present
        });

        // Log removed for production
        return [
            'user' => $user2,
            'messages' => $messages,
        ];
    }

    public function deleteMessage(Message $message): bool
    {
        try {
            Log::info('🗑️ Deleting message', [
                'message_id' => $message->id,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id
            ]);
            
            // Delete associated media files
            foreach ($message->media as $media) {
                $this->deleteMediaFile($media->url);
                
                foreach ($media->previews as $preview) {
                    $this->deleteMediaFile($preview->url);
                }
                
                Log::info('🗑️ Deleted media files for media', [
                    'media_id' => $media->id
                ]);
            }
            
            $result = $this->messageRepository->delete($message);
            
            Log::info('🗑️ Message deletion result: ' . ($result ? 'Success' : 'Failed'), [
                'message_id' => $message->id
            ]);
            
            return $result;
        } catch (\Exception $e) {
            Log::error('❌ Error in deleteMessage: ' . $e->getMessage(), [
                'message_id' => $message->id,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    protected function deleteMediaFile(string $url): void
    {
        try {
            Log::info('🗑️ Attempting to delete media file', [
                'url' => $url,
            ]);

            $this->mediaStorage->delete($url);
        } catch (\Exception $e) {
            Log::error('❌ Error in deleteMediaFile: ' . $e->getMessage(), [
                'url' => $url,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function getUnreadMessagesCount(User $user): int
    {
        try {
            Log::info('🔢 Getting unread messages count', [
                'user_id' => $user->id
            ]);
            
            $count = $this->messageRepository->getUnreadMessagesCount($user);
            
            Log::info('✅ Unread messages count: ' . $count, [
                'user_id' => $user->id
            ]);
            
            return $count;
        } catch (\Exception $e) {
            Log::error('❌ Error in getUnreadMessagesCount: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }

    public function getRecentConversations(User $user, int $limit = 10): array
    {
        try {
            Log::info('📋 Getting recent conversations', [
                'user_id' => $user->id,
                'limit' => $limit
            ]);
            
            $conversations = $this->messageRepository->getRecentConversations($user, $limit);
            
            Log::info('✅ Retrieved ' . count($conversations) . ' recent conversations', [
                'user_id' => $user->id
            ]);

            // Transform the conversations to the expected format
            $formattedConversations = $conversations->map(function ($conversation) {
                $otherUser = $conversation->other_user;

                // Get the last message with media
                $lastMessage = [
                    'content' => $conversation->content,
                    'created_at' => $conversation->created_at,
                ];

                // Add media if it exists
                if (isset($conversation->message_id)) {
                    $message = Message::find($conversation->message_id);
                    if ($message) {
                        $media = $message->media()->with('previews')->get();
                        if ($media->count() > 0) {
                            $lastMessage['media'] = $media->map(function ($mediaItem) {
                                return [
                                    'id' => $mediaItem->id,
                                    'url' => $mediaItem->full_url,
                                    'type' => $mediaItem->type
                                ];
                            })->toArray();
                        }
                    }
                }

                return [
                    'id' => $conversation->id,
                    'user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        'avatar' => $otherUser->avatar,
                        'username' => $otherUser->username,
                        'isSubscriber' => $otherUser->isSubscriber ?? false,
                        'handle' => $otherUser->handle ?? '',
                    ],
                    'lastMessage' => $lastMessage,
                ];
            })->values()->toArray();

            return $formattedConversations;
        } catch (\Exception $e) {
            Log::error('❌ Error in getRecentConversations: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function searchUsers(string $query): Collection
    {
        try {
            Log::info('🔍 Searching for users', [
                'query' => $query
            ]);
            
            $users = User::where('name', 'like', "%{$query}%")
                ->orWhere('username', 'like', "%{$query}%")
                ->limit(10)
                ->get();
            
            Log::info('✅ Found ' . $users->count() . ' users matching query', [
                'query' => $query
            ]);
            
            return $users;
        } catch (\Exception $e) {
            Log::error('❌ Error in searchUsers: ' . $e->getMessage(), [
                'query' => $query,
                'trace' => $e->getTraceAsString()
            ]);
            return collect([]);
        }
    }

    protected function createMessagePermissions(Message $message, array $permissions)
    {
        foreach ($permissions as $permissionGroup) {
            // Create a permission set for the message
            $permissionSet = new PermissionSet();
            $permissionSet->permissionable_id = $message->id;
            $permissionSet->permissionable_type = Message::class;
            $permissionSet->save();
            
            Log::info('🔒 Created permission set', [
                'permission_set_id' => $permissionSet->id,
                'message_id' => $message->id
            ]);

            foreach ($permissionGroup as $permission) {
                $permissionSet->permissions()->create([
                    'type' => $permission['type'],
                    'value' => $this->formatPermissionValue($permission),
                ]);
                
                Log::info('🔒 Added permission', [
                    'type' => $permission['type'],
                    'value' => $permission['value'] ?? null
                ]);
            }
        }
    }

    protected function formatPermissionValue($permission)
    {
        if ($permission['type'] === 'limited_time') {
            return json_encode($permission['value']);
        }

        return $permission['value'];
    }

    protected function processMediaFiles(array $data): array
    {
        if (isset($data['media']) && is_array($data['media'])) {
            Log::info('🖼️ Processing media files: ' . count($data['media']));
            
            foreach ($data['media'] as &$mediaItem) {
                if (isset($mediaItem['file']) && $mediaItem['file'] instanceof UploadedFile) {
                    Log::info('📁 Processing uploaded file', [
                        'original_name' => $mediaItem['file']->getClientOriginalName(),
                        'mime_type' => $mediaItem['file']->getMimeType(),
                        'size' => $mediaItem['file']->getSize()
                    ]);
                    
                    $mediaItem['url'] = $this->storeMedia($mediaItem['file'], 'messages');
                    $mediaItem['type'] = $mediaItem['type'] ??
                        (str_starts_with($mediaItem['file']->getMimeType(), 'image/') ? 'image' : 'video');
                    unset($mediaItem['file']);
                    
                    Log::info('✅ File processed', [
                        'url' => $mediaItem['url'],
                        'type' => $mediaItem['type']
                    ]);
                }

                if (isset($mediaItem['previewVersions']) && is_array($mediaItem['previewVersions'])) {
                    Log::info('🔍 Processing preview versions: ' . count($mediaItem['previewVersions']));
                    
                    $mediaItem['previews'] = [];
                    foreach ($mediaItem['previewVersions'] as $previewFile) {
                        if ($previewFile instanceof UploadedFile) {
                            $previewUrl = $this->storeMedia($previewFile, 'previews');
                            $mediaItem['previews'][] = $previewUrl;
                            
                            Log::info('✅ Preview processed', [
                                'url' => $previewUrl
                            ]);
                        }
                    }
                    unset($mediaItem['previewVersions']);
                }
            }
        }

        return $data;
    }

    protected function storeMedia(UploadedFile $file, string $directory = 'messages'): string
    {
        $filename = $this->generateUniqueFilename($file);
        Log::info('📁 Storing media file', [
            'filename' => $filename,
            'directory' => $directory
        ]);
        
        // Use MediaService to process the file with watermark support
        $type = str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'video';
        
        // Get the authenticated user's watermark text
        $watermarkText = null;
        $userId = null;
        try {
            $user = auth()->user();
            if ($user) {
                $watermarkText = $user->media_watermark ?? $user->username;
                $userId = $user->id;
            }
        } catch (\Exception $e) {
            Log::warning('Could not get authenticated user for watermark', ['error' => $e->getMessage()]);
        }
        
        // Log removed for production
        
        // Use MediaService to process the file
        $path = $this->mediaService->processMedia($file, $watermarkText, $type);
        
        // Log removed for production

        return $path;
    }

    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->timestamp;
        $randomString = Str::random(8);

        return "{$originalName}_{$timestamp}_{$randomString}.{$extension}";
    }

    /**
     * Check if tipping is required for sending a message to a creator
     */
    protected function checkTippingRequirement(User $sender, User $receiver): void
    {
        // Only check if receiver is a creator
        if ($receiver->role !== 'creator') {
            return;
        }

        // Get receiver's messaging settings
        $requireTipForMessages = $receiver->getSetting('messaging', 'require_tip_for_messages', false);
        $acceptMessagesFromFollowed = $receiver->getSetting('messaging', 'accept_messages_from_followed', true);

        // Log removed for production

        // If receiver doesn't require tips, allow the message
        if (!$requireTipForMessages) {
            // Log removed for production
            return;
        }

        // If sender is also a creator, check if receiver requires tips from creators
        if ($sender->role === 'creator') {
            // Log removed for production
            // For now, creators can send to other creators without tips
            // This can be modified based on business requirements
            return;
        }

        // Check if there's a mutual follow relationship
        $senderFollowsReceiver = $sender->following()->where('followed_id', $receiver->id)->exists();
        $receiverFollowsSender = $receiver->following()->where('followed_id', $sender->id)->exists();
        $mutualFollow = $senderFollowsReceiver && $receiverFollowsSender;

        // Log removed for production

        // If receiver accepts messages from followed users and there's a mutual follow
        if ($acceptMessagesFromFollowed && $mutualFollow) {
            // Log removed for production
            return;
        }

        // Get the time of the last message the sender sent to this receiver
        $lastMessageTime = Message::where('sender_id', $sender->id)
            ->where('receiver_id', $receiver->id)
            ->latest('created_at')
            ->value('created_at');

        // Check if sender has an unconsumed tip for messaging that is newer than the last message (if any)
        $unconsumedTipExists = Tip::where('sender_id', $sender->id)
            ->where('recipient_id', $receiver->id)
            ->where('tippable_type', 'message')
            ->whereNull('tippable_id')
            ->when($lastMessageTime, function ($q) use ($lastMessageTime) {
                $q->where('created_at', '>', $lastMessageTime);
            })
            ->exists();

        if ($unconsumedTipExists) {
            Log::info('✅ Unconsumed tip (newer than last message) found, allowing message without additional tip');
            return;
        }

        // If we reach here, a tip is required
        Log::warning('💰 Tip required for message', [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'reason' => 'Creator requires tips and no mutual follow relationship'
        ]);

        throw new \Exception('A tip is required to send a message to this creator. Please send a tip first.');
    }
}
