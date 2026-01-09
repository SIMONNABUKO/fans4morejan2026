<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Message;
use App\Models\Media;
use App\Models\PermissionSet;
use App\Models\AutomatedMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Events\NewMessageEvent;

class SendAutomatedMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $senderId;
    protected $recipientId;
    protected $messageData;

    /**
     * Create a new job instance.
     */
    public function __construct($senderId, $recipientId, array $messageData)
    {
        $this->senderId = $senderId;
        $this->recipientId = $recipientId;
        $this->messageData = $messageData;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            Log::info('Starting automated message job', [
                'sender_id' => $this->senderId,
                'recipient_id' => $this->recipientId,
                'automated_message_id' => $this->messageData['automated_message_id'] ?? null
            ]);

            // If we have an automated_message_id, fetch the automated message
            if (empty($this->messageData['automated_message_id'])) {
                Log::warning('No automated_message_id provided, cannot send automated message');
                return;
            }
            
            $automatedMessage = AutomatedMessage::with(['media.previews', 'permissionSets.permissions'])
                ->find($this->messageData['automated_message_id']);
            
            if (!$automatedMessage) {
                Log::warning('Automated message not found', [
                    'automated_message_id' => $this->messageData['automated_message_id']
                ]);
                return;
            }
            
            // Check if the automated message is active
            if (!$automatedMessage->is_active) {
                Log::info('Skipping inactive automated message', [
                    'automated_message_id' => $automatedMessage->id
                ]);
                return;
            }
            
            // Get the sender and recipient
            $sender = User::findOrFail($this->senderId);
            $recipient = User::findOrFail($this->recipientId);
            
            // Create everything in a transaction
            DB::beginTransaction();
            try {
                // 1. Create the message
                $message = new Message();
                $message->sender_id = $this->senderId;
                $message->receiver_id = $this->recipientId;
                $message->content = $this->messageData['content'] ?? $automatedMessage->content;
                $message->save();
                
                Log::info('Created new message', [
                    'message_id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'receiver_id' => $message->receiver_id
                ]);
                
                // 2. Create media records for the message
                if ($automatedMessage->media->isNotEmpty()) {
                    foreach ($automatedMessage->media as $automatedMedia) {
                        // Create a new media record
                        $media = new Media();
                        $media->user_id = $this->senderId;
                        $media->mediable_id = $message->id;
                        $media->mediable_type = Message::class;
                        $media->type = $automatedMedia->type;
                        $media->url = $automatedMedia->url;
                        $media->save();
                        
                        Log::info('Created new media for message', [
                            'media_id' => $media->id,
                            'message_id' => $message->id,
                            'url' => $media->url
                        ]);
                        
                        // Create preview records if they exist
                        if ($automatedMedia->previews->isNotEmpty()) {
                            foreach ($automatedMedia->previews as $automatedPreview) {
                                $preview = $media->previews()->create([
                                    'url' => $automatedPreview->url
                                ]);
                                
                                Log::info('Created new preview for media', [
                                    'media_id' => $media->id,
                                    'preview_id' => $preview->id,
                                    'url' => $preview->url
                                ]);
                            }
                        }
                    }
                }
                
                // 3. Create permission sets and permissions for the message
                if ($automatedMessage->permissionSets->isNotEmpty()) {
                    foreach ($automatedMessage->permissionSets as $automatedPermissionSet) {
                        // Create a new permission set
                        $permissionSet = new PermissionSet();
                        $permissionSet->permissionable_id = $message->id;
                        $permissionSet->permissionable_type = Message::class;
                        $permissionSet->save();
                        
                        Log::info('Created new permission set for message', [
                            'permission_set_id' => $permissionSet->id,
                            'message_id' => $message->id
                        ]);
                        
                        // Create permission records
                        foreach ($automatedPermissionSet->permissions as $automatedPermission) {
                            $permission = $permissionSet->permissions()->create([
                                'type' => $automatedPermission->type,
                                'value' => $automatedPermission->value
                            ]);
                            
                            Log::info('Created new permission for permission set', [
                                'permission_id' => $permission->id,
                                'permission_set_id' => $permissionSet->id,
                                'type' => $permission->type,
                                'value' => $permission->value
                            ]);
                        }
                    }
                }
                
                // 4. Load relationships for the event
                $message->load('media.previews', 'sender', 'receiver');
                
                // 5. Broadcast the new message event
                event(new NewMessageEvent($message));
                
                DB::commit();
                
                Log::info('Automated message sent successfully', [
                    'message_id' => $message->id,
                    'sender_id' => $this->senderId,
                    'recipient_id' => $this->recipientId,
                    'media_count' => $automatedMessage->media->count(),
                    'permission_sets_count' => $automatedMessage->permissionSets->count()
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Failed to send automated message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'sender_id' => $this->senderId,
                'recipient_id' => $this->recipientId
            ]);

            // Determine if we should retry
            if ($this->attempts() < 3) {
                $this->release(30); // Try again in 30 seconds
            } else {
                $this->fail($e);
            }
        }
    }
}