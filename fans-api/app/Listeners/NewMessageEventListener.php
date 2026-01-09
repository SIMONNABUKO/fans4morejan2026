<?php

namespace App\Listeners;

use App\Events\NewMessageEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NewMessageEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\NewMessageEvent  $event
     * @return void
     */
    public function handle(NewMessageEvent $event)
    {
        Log::info('📥 NewMessageEventListener handling event', [
            'message_id' => $event->message->id,
            'sender_id' => $event->message->sender_id,
            'receiver_id' => $event->message->receiver_id
        ]);
        
        // You can add additional processing here if needed
        // For example, sending push notifications, emails, etc.
    }
}

