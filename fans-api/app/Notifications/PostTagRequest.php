<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\PostTag;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostTagRequest extends Notification implements ShouldQueue
{
    use Queueable;

    protected $tagger;
    protected $post;
    protected $tag;
    protected $notificationData = [];

    /**
     * Create a new notification instance.
     */
    public function __construct(User $tagger, $post, PostTag $tag)
    {
        $this->tagger = $tagger;
        $this->post = $post;
        $this->tag = $tag;
        
        // Log the constructor parameters
        Log::info('PostTagRequest constructor called', [
            'tagger_id' => $tagger->id,
            'post_id' => $post->id,
            'tag_id' => $tag->id
        ]);
        
        // IMPORTANT: Create the notification data immediately but keep it flat
        try {
            // Create a flatter notification data structure to avoid nesting
            $this->notificationData = [
                'type' => 'tag_request',
                'tagger_id' => $tagger->id,
                'tagger_name' => $tagger->name,
                'tagger_username' => $tagger->username ?? null,
                'post_id' => $post->id,
                'post' => [
                    'id' => $post->id,
                    'content' => $post->content ?? '',
                    'media' => $post->media->map(function ($media) {
                        return [
                            'id' => $media->id,
                            'url' => $media->url,
                            'type' => $media->type,
                            'previews' => $media->previews->map(function ($preview) {
                                return [
                                    'id' => $preview->id,
                                    'url' => $preview->url,
                                    'type' => $preview->type
                                ];
                            })->toArray()
                        ];
                    })->toArray(),
                    'user' => [
                        'id' => $post->user->id,
                        'name' => $post->user->name,
                        'username' => $post->user->username,
                        'avatar' => $post->user->avatar,
                        'verified' => $post->user->verified ?? false
                    ],
                    'created_at' => $post->created_at,
                    'stats' => [
                        'total_likes' => $post->stats->total_likes ?? 0,
                        'total_comments' => $post->stats->total_comments ?? 0
                    ]
                ],
                'tag' => [
                    'id' => $tag->id,
                    'status' => $tag->status ?? 'pending',
                    'updated_at' => $tag->updated_at ?? now()
                ],
                'message' => "{$tagger->name} wants to tag you in a post"
            ];
            
            Log::info('Flat notification data prepared', $this->notificationData);
            
            // Store serialized size for debugging
            $serializedSize = strlen(json_encode($this->notificationData));
            Log::info('Notification data size', ['size_bytes' => $serializedSize]);
            
        } catch (\Exception $e) {
            Log::error('Error in PostTagRequest constructor', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        Log::info('PostTagRequest via method called', [
            'notifiable_id' => $notifiable->id
        ]);
        
        // Directly insert notification into database with intentionally flattened structure
        try {
            $id = Str::uuid()->toString();
            $jsonData = json_encode($this->notificationData);
            
            // Log the exact JSON that will be stored
            Log::info('Notification data being stored', [
                'json' => $jsonData,
                'size' => strlen($jsonData)
            ]);
            
            // Check database column definition
            try {
                $columnInfo = DB::select("SHOW COLUMNS FROM notifications WHERE Field = 'data'");
                Log::info('Notifications table data column info', ['column_info' => $columnInfo]);
            } catch (\Exception $e) {
                Log::warning('Could not retrieve column info', ['error' => $e->getMessage()]);
            }
            
            DB::table('notifications')->insert([
                'id' => $id,
                'type' => 'App\\Notifications\\PostTagRequest',
                'notifiable_type' => get_class($notifiable),
                'notifiable_id' => $notifiable->id,
                'data' => $jsonData,
                'created_at' => now(),
                'updated_at' => now(),
                'read_at' => null
            ]);
            
            // Immediately verify what was saved
            try {
                $savedNotification = DB::table('notifications')
                    ->where('id', $id)
                    ->first();
                
                // Log what was actually saved
                if ($savedNotification) {
                    Log::info('Notification as saved in database', [
                        'id' => $savedNotification->id,
                        'data' => $savedNotification->data,
                        'saved_data_length' => strlen($savedNotification->data)
                    ]);
                } else {
                    Log::warning('Could not retrieve saved notification');
                }
            } catch (\Exception $e) {
                Log::warning('Error retrieving saved notification', ['error' => $e->getMessage()]);
            }
            
            Log::info('Successfully inserted notification data directly', [
                'notification_id' => $id,
                'data_keys' => array_keys($this->notificationData)
            ]);
            
            return ['broadcast']; // Skip Laravel's database channel since we inserted directly
            
        } catch (\Exception $e) {
            Log::error('Failed to insert notification directly', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return ['database', 'broadcast']; // Fallback to Laravel's default channels
        }
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        Log::info('PostTagRequest toArray method called', [
            'notifiable_id' => $notifiable->id
        ]);
        
        // In case this actually gets called, use our pre-generated data
        return $this->notificationData;
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        Log::info('PostTagRequest toBroadcast method called', [
            'notifiable_id' => $notifiable->id
        ]);
        
        // Use the pre-generated data for broadcasting
        return new BroadcastMessage($this->notificationData);
    }
    
    /**
     * Get the data for storing in the database.
     */
    public function toDatabase(object $notifiable): array
    {
        Log::info('PostTagRequest toDatabase method called', [
            'notifiable_id' => $notifiable->id
        ]);
        
        // In case this actually gets called, use our pre-generated data
        return $this->notificationData;
    }
}