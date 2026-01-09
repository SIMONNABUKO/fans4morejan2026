<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Http\Resources\MessageResource;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\Log;

class NewMessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        Log::info('📩 Creating NewMessageEvent', [
            'message_id' => $message->id,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id
        ]);
        
        // Load relationships to ensure they're available in the broadcast
        $message->load(['media.previews', 'sender', 'receiver']);
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        Log::info('📢 Broadcasting message to channels', [
            'sender_channel' => 'user.' . $this->message->sender_id,
            'receiver_channel' => 'user.' . $this->message->receiver_id,
            'message_id' => $this->message->id,
            'event_name' => 'new.message'
        ]);
        
        // Broadcast to both sender and receiver public channels
        // Changed from PrivateChannel to Channel for public channels
        return [
            new Channel('user.' . $this->message->sender_id),
            new Channel('user.' . $this->message->receiver_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        // This is the event name that will be used
        return 'new.message';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        // Format the message using the MessageResource
        $data = (new MessageResource($this->message))->resolve();
        
        Log::info('📤 Broadcasting message data', [
            'message_id' => $this->message->id,
            'content_length' => strlen($this->message->content ?? ''),
            'has_media' => $this->message->media->count() > 0,
            'event_name' => 'new.message',
            'data_sample' => json_encode(array_slice($data, 0, 3)) // Log a sample of the data
        ]);
        
        return $data;
    }
}

