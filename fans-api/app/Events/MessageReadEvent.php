<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MessageReadEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**p
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        Log::info('👁️ Creating MessageReadEvent', [
            'message_id' => $message->id,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id,
            'read_at' => $message->read_at
        ]);
        
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        Log::info('📢 Broadcasting read receipt to channel', [
            'sender_channel' => 'user.' . $this->message->sender_id,
            'event_name' => 'message.read'
        ]);
        
        // Broadcast to the sender's public channel
        // Changed from PrivateChannel to Channel for public channels
        return [
            new Channel('user.' . $this->message->sender_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.read';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        $data = [
            'id' => $this->message->id,
            'read_at' => $this->message->read_at,
        ];
        
        Log::info('📤 Broadcasting read receipt data', array_merge($data, [
            'event_name' => 'message.read'
        ]));
        
        return $data;
    }
}

